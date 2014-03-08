<?php
class Statistics extends CComponent {


	public $adjacencies = array();
	public $dyads = array();
	public $components = array();
	public $nodeComponents = array();
	public $nodes = array();
	public $connections = array();
	public $names = array();
	public $isolates = array();
	public $shortPaths = array();
	public $aMatrix = array();
	public $expressionId;
	public $interviewId;
	public $closenesses = array();
	public $alters = array();
	public $eigenvectorCentralities = array();

	/*
	 * Density is the number of connections in the actual
	 * network divided by the number of possible connections
	 * for networks with that number of nodes.
	 */

	public function initComponents($interviewId, $expressionId){
		$alters = Alters::model()->findAllByAttributes(array('interviewId'=>$interviewId));
		$this->alters = $alters;
		$alters2 = $alters;
		$expression = new Expression;
		$this->expressionId = $expressionId;
		$this->interviewId = $interviewId;
		foreach($alters as $alter){
			$this->names[$alter->id] = $alter->name;
	   		array_shift($alters2);
			foreach($alters2 as $alter2){
				if($expression->evalExpression($expressionId, $interviewId, $alter->id, $alter2->id)){
					if(!in_array($alter->id, $this->nodes))
						$this->nodes[] = $alter->id;
					if(!in_array($alter2->id, $this->nodes))
						$this->nodes[] = $alter2->id;
					$this->adjacencies[] = array($alter->id, $alter2->id);
					$this->connections[$alter2->id][] = $alter->id;
					$this->connections[$alter->id][] =  $alter2->id;
				}
			}
		}

		foreach($alters as $alter){
			if(!in_array($alter->id, $this->nodes)){
				$this->isolates[] = $alter->id;
				$this->nodes[] = $alter->id;
				$this->connections[$alter->id] = array();
			}
		}

		foreach($this->adjacencies as $dyad){
			if(count($this->components) == 0){
				$this->components[] = array(0=>$dyad);
				$this->nodeComponents[$dyad[0]] = 0;
				$this->nodeComponents[$dyad[1]] = 0;
				continue;
			}else{
				foreach($this->components as $index=>&$component){
					$inComponent = false;
					foreach($component as $cDyad){
						if(in_array($dyad[0], $cDyad) || in_array($dyad[1], $cDyad))
							$inComponent = true;
					}
					if($inComponent){
						$this->nodeComponents[$dyad[0]] = $index;
						$this->nodeComponents[$dyad[1]] = $index;
						$component[] = $dyad;
						break;
					}else{
						$this->components[] = array(0=>$dyad);
					}
				}
			}
		}

		foreach($this->components as $index=>$component){
			if(count($component) == 1){
				$this->dyads[] = $component[0];
				unset($this->components[$index]);
			}
		}

		$endNodes = $this->nodes;
		foreach($this->nodes as $node){
			array_shift($endNodes);
			foreach($endNodes as $endNode){
				$this->getDistance(array($node), $endNode);
			}
		}

	}

	public function getDistance($visited, $node2, $trail = array()){
		$node1 =  $visited[count($visited)-1];
		if(count($trail) == 0)
			$trail[] = $node1;
		if(in_array($node2, $this->connections[$node1])){
			if(!isset($this->shortPaths[md5($visited[0] . $node2)]) && !isset($this->shortPaths[md5($node2.$visited[0])])){
				$trail[] = $node2;
				$this->shortPaths[md5($visited[0] . $node2)][] = $trail;
			}else{

				if(isset($this->shortPaths[md5($visited[0] . $node2)]))
					$key = md5($visited[0] . $node2);
				else
					$key = md5($node2. $visited[0]);

				if(count($trail) < count($this->shortPaths[$key][0]) -1)
					$this->shortPaths[$key] = array();

				if(count($this->shortPaths[$key]) == 0 || count($trail) == count($this->shortPaths[$key][0]) -1){
					$trail[] = $node2;
					$this->shortPaths[$key][] = $trail;
				}
			}
		}else{
			foreach($this->connections[$node1] as $endNode){
				if(!in_array($endNode, $visited)){
					$visited[] = $endNode;
					$this->getDistance($visited, $node2, array_merge($trail,array($endNode)));
				}
		    }
		}
	}

	public function getDegree($alterId){
		if(isset($this->connections[$alterId]))
			return count($this->connections[$alterId]);
		else
			return 0;
	}

	public function getBetweenness($alterId){
		$sum = 0;
		$otherNodes = array_diff($this->nodes, array($alterId));
		$visited = array();
		$endNodes = $otherNodes;

		foreach($otherNodes as $node){
			foreach($endNodes as $endNode){
				if($node == $endNode)
					continue;
				$all = 0; $between = 0;
				if(isset($this->shortPaths[md5($node.$endNode)]))
					$key = md5($node.$endNode);
				elseif(isset($this->shortPaths[md5($node.$endNode)]))
					$key = md5($endNode.$node);
				else
					continue;
				foreach($this->shortPaths[$key] as $path){
					if(in_array($alterId, $path))
						$between++;
					$all++;
				}
				if($all != 0)
					$sum = $sum + floatval($between/ $all);
			}
		}
		return round($sum,3);
	}

	private function getPaths($node1, $node2){
		if(isset($this->shortPaths[md5($node1.$node2)]))
			$key = md5($node1.$node2);
		if(isset($this->shortPaths[md5($node2.$node1)]))
			$key = md5($node2.$node1);
		if(!isset($key))
			return 0;

		$distance = count($this->shortPaths[$key][0]) - 1;
		if($distance < 1)
			return 1;
		$paths = 0;
		foreach($this->connections[$node2] as $n){
			if($n == $node1)
				continue;
			$key2 = '';
			if(isset($this->shortPaths[md5($n.$node2)]))
				$key2 = md5($n.$node2);
			elseif(isset($this->shortPaths[md5($node2.$n)]))
				$key2 = md5($node2.$n);
			else
				continue;
			if(count($this->shortPaths[$key2][0]) - 1 < $distance)
				$paths = $paths + $this->getPaths($n, $node2);
		}
		return $paths;
	}

	public function maxBetweenness(){
		$max = array();
		foreach($this->nodes as $node){
			$max[] = $this->getBetweenness($node);
		}
		if(count($max) > 0)
			return max($max);
		else
			return false;
	}

	public function maxDegree(){
		$max = array();
		if(count($this->nodes) > 0){
			foreach($this->nodes as $node){
				$max[] = count($this->connections[$node]);
			}
			return max($max);
		}else{
			return false;
		}
	}

	public function getDensity() {
		$possibleEdges = 0;
		for($i = 0; $i < count($this->alters); $i++) {
			$possibleEdges = $possibleEdges + $i;
		}
		$density = count($this->adjacencies) < 1 ? 0.0 : count($this->adjacencies) / $possibleEdges;
		return round($density,3);
	}

	public function getCloseness($alterId){
		$total = 0; $reachable = 0;
		foreach($this->nodes as $node){
			if(isset($this->shortPaths[md5($alterId.$node)]) || isset($this->shortPaths[md5($node.$alterId)])){
				if(isset($this->shortPaths[md5($alterId.$node)]))
					$distance = count($this->shortPaths[md5($alterId.$node)][0]) - 1;
				else
					$distance = count($this->shortPaths[md5($node.$alterId)][0]) - 1;

				$total = $total + $distance;
				$reachable++;
			}
		}
		if($reachable < 1)
			return 0.0;

		$average = $total / $reachable;
		return $reachable / ($average * (count($this->nodes) - 1));
	}

	public function getClosenesses(){
		foreach($this->alters as $node){
			$closeness = $this->getCloseness($node->id);
			$this->closenesses[$node->id] = $closeness;
		}
		return $this->closenesses;
	}

	private function nextEigenvectorGuess($guess) {
		$results = array();
		foreach($guess as $gNode=>$value) {
			$result = 0.0;
			if(isset($this->connections[$gNode])){
				foreach($this->connections[$gNode] as $neighbor) {
					$result = $result + $guess[$neighbor];
				}
			}
			$results[$gNode] = $result;
		}
		return $this->normalize($results);
	}

	private $tinyNum = 0.0000001;
	private function normalize($vec) {
		$magnitudeSquared = 0.0;
		foreach($vec as $gNode=>$value) {
			//echo $magnitudeSquared ."+" . pow($value,2) . "=";
			$magnitudeSquared = $magnitudeSquared + pow($value,2);
			//echo "magnitude squared:".$magnitudeSquared."<br>";
		}
		$magnitude =  sqrt($magnitudeSquared);
		$factor = 1 / ($magnitude < $this->tinyNum ? $this->tinyNum : $magnitude);
		//echo "magnitude:".$magnitude.":factor:$factor<br>";
		$normalized = array();
		foreach($vec as $gNode=>$value) {
			$normalized[$gNode] = $value  * $factor;
		}
		return $normalized;
	}
	/*
	private Map<N,Double> normalize(Map<N,Double> vec) {
		Double magnitudeSquared = 0.0;
		for(Double component : vec.values()) {
			magnitudeSquared += component * component;
		}
		Double magnitude = Math.sqrt(magnitudeSquared);
		Double factor = 1 / (magnitude < tinyNum ? tinyNum : magnitude);
		Map<N,Double> normalized = Maps.newHashMap();
		for(N node : vec.keySet()) {
			normalized.put(node, vec.get(node)*factor);
		}
		return normalized;
	}*/
	public function eigenvectorCentrality($node = null) {
		if(count($this->eigenvectorCentralities) == 0) {
			$tries = (count($this->nodes)+5)*(count($this->nodes)+5);
			$guess = $this->getClosenesses();
			while($tries >= 0) {
				$nextGuess = $this->nextEigenvectorGuess($guess);
				if($this->change($guess,$nextGuess) < $this->tinyNum || $tries == 0) {
					$this->eigenvectorCentralities = $nextGuess;
				}
				$guess = $nextGuess;
				$tries--;
			}
		}
		if($node != null && isset($this->eigenvectorCentralities[$node]))
			return $this->eigenvectorCentralities[$node] < sqrt($this->tinyNum) ? 0.0 : round($this->eigenvectorCentralities[$node],3);
	}

	private function change($vec1, $vec2) {
		$total = 0.0;
		foreach($vec1 as $node=>$value) {
			$total = $total + abs($vec1[$node] - $vec2[$node]);

		}
		return $total;
	}

	public function maxEigenvector(){
		$max = array();
		if(count($this->eigenvectorCentralities) == 0)
			$this->eigenvectorCentrality();
		foreach($this->eigenvectorCentralities as $node=>$value){
			$max[] = $value;
		}
		if(count($max) > 0)
			return round(max($max),3);
		else
			return false;
	}

	public function degreeMaxDiff() {
		$nodes = count($this->nodes);
		return $nodes < 3 ? null : ($nodes-1) * ($nodes-2);
		//return $nodes < 3 ? null : ($nodes-1) * (1 - 1.0/($nodes-1));
	}

	public function degreeCentralization() {
		$max = $this->maxDegree();
		$total = 0.0;
		foreach($this->nodes as $node) {
			$total = $total + $max - $this->getDegree($node);
		}
		return count($this->nodes) < 3 ? 0.0 :
			round($total / $this->degreeMaxDiff(),3);
	}

	public function betweennessMaxDiff() {
		$nodes = count($this->nodes);
		return $nodes < 3 ? null : ($nodes-1)*($nodes-1)*($nodes-2) / 2.0;
	}

	public function betweennessCentralization() {
		$max = $this->maxBetweenness();
		$total = 0.0;
		foreach($this->nodes as $node) {
			$total = $total + $max - $this->getBetweenness($node);
		}
		return count($this->nodes) < 3 ? 0.0 :
			round($total / $this->betweennessMaxDiff(),3);
	}

	/*
	public function initComponents() {
		if($components == null) {
			isolates = Sets.newHashSet();
			dyads = Sets.newHashSet();
			components = Sets.newHashSet();
			Set<N> nodes = network.getNodes();
			for(N seed : nodes) {
				Boolean seedRepresentsNewComponent = true;
				for(Set<N> component : components) {
					if(component.contains(seed)) {
						seedRepresentsNewComponent = false;
					}
				}
				if(seedRepresentsNewComponent) {
					Set<N> component = new HashSet<N>();
					for(N node : nodes) {
						if(network.distance(seed, node) != null) {
							component.add(node);
						}
					}
					components.add(Collections.unmodifiableSet(component));
					if(component.size() < 2) {
						isolates.add(seed);
					} else if(component.size() < 3) {
						dyads.add(Collections.unmodifiableSet(component));
					}
				}
			}
		}
	}

	private Set<N> isolates;
	private Set<Set<N>> dyads;
	private Set<Set<N>> components;

	public Set<N> isolates() {
		initComponents();
		return isolates;
	}

	public Set<Set<N>> dyads() {
		initComponents();
		return dyads;
	}

	public Set<Set<N>> components() {
		initComponents();
		return components;
	}

	/*
	 * For each of these properties x, define two methods:
	 * 1) Double xCentrality(N node)
	 * 2) Double xCentralityMaxDifference(Integer nodes)
	 *
	 * Where xMaxCentralityDifference gives the maximum possible
	 * sum of centrality differences for that many nodes. That
	 * maximum is typically realized for a star network, in which
	 * one node connects to all others but none of the others connect
	 * to each other.
	 */
	//public static String[] centralityProperties = {"degree","closeness","betweenness","eigenvector"};

	// Implement the following with reflection. See deprecated *betweenness* methods for examples.
	/*
	public function centrality(String property, N node) {
		try {
			Method centralityMethod =
				Statistics.class.getDeclaredMethod(
						property+"Centrality",
						new Class[]{Object.class}); // Would be node.getClass() except generics erased at runtime.
			return (Double) centralityMethod.invoke(this, node);
		} catch (Exception ex) {
			throw new RuntimeException("Unable to determine "+property+"Centrality for "+node,ex);
		}
	}

	public Double centralityMean(String property) {
		Double total = 0.0;
		for(N node : network.getNodes()) {
			total += centrality(property,node);
		}
		return network.getNodes().size() < 1 ? 0.0 : total / network.getNodes().size();
	}

	public Double maxCentrality(String property) {
		N node = maxCentralityNode(property);
		return node == null ? 0.0 : centrality(property,node);
	}

	public N maxCentralityNode(String property) {
		Double maxValue = null;
		N maxNode = null;
		for(N node : network.getNodes()) {
			if(maxValue == null || centrality(property,node) > maxValue) {
				maxValue = centrality(property,node);
				maxNode = node;
			}
		}
		return maxNode;
	}

	public Double centralization(String property) {
		Double maximumCentrality = maxCentrality(property);
		Double totalCentralityDifference = 0.0;
		Set<N> nodes = network.getNodes();
		for(N node : nodes) {
			totalCentralityDifference += maximumCentrality - centrality(property,node);
		}
		Integer n = nodes.size();
		return n < 3 ? 0.0 :
			totalCentralityDifference / centralityMaxDifference(property, n);
	}

	public static Double centralityMaxDifference(String property, Integer nodes) {

		try {
			Method centralityMethod =
				Statistics.class.getDeclaredMethod(
						property+"CentralityMaxDifference",
						new Class[]{Integer.class});
			return (Double) centralityMethod.invoke(null, nodes);
		} catch (Exception ex) {
			throw new RuntimeException("Unable to determine "+property+"CentralityMaxDifference for "+nodes+" nodes.",ex);
		}
	}

	/*
	 * Degree centrality is the number of direct connections
	 * to a node divided by the number of possible direct
	 * connection
	 /s to a node in a network of that size.

	public Double degreeCentrality(N node) {
		Integer nodes = network.getNodes().size();
		return nodes < 2 ? 0.0 : network.connections(node).size() * 1.0 / (nodes-1);
	}
	public static Double degreeCentralityMaxDifference(Integer nodes) {
		return nodes < 3 ? null : (nodes-1) * (1 - 1.0/(nodes-1));
	}

	/*
	 * For fully connected network, closeness is the reciprocal
	 * of the average distance to other nodes. For disconnected
	 * networks, it is the closeness within a component multiplied
	 * by the portion of other nodes that are in that component.

	public Double closenessCentrality(N node) {
		if(! nodeToCloseness.containsKey(node)) {
			Integer reachable = 0;
			Integer totalDistance = 0;
			Set<N> nodes = network.getNodes();
			for(N n : nodes) {
				Integer distance = network.distance(node, n);
				if(distance != null && distance > 0) {
					reachable++;
					totalDistance += distance;
				}
			}
			if(reachable < 1) {
				return 0.0;
			}
			Double averageDistance = totalDistance*1.0/reachable;
			nodeToCloseness.put(node, reachable / (averageDistance * (nodes.size()-1)));
		}
		return nodeToCloseness.get(node);
	}
	private Map<N,Double> nodeToCloseness = Maps.newHashMap();
	public static Double closenessCentralityMaxDifference(Integer nodes) {
		return nodes < 3 ? null : (nodes-2) * (nodes-1) / (2*nodes - 3.0);
	}

	/*
	 * Sum over pairs of nodes a,b (such that none of a,b,n are equal)
	 * of the number of shortest paths from a to b that pass through
	 * n divided by the total number of shortest paths from a to b.
	 * Disconnected networks are addressed by choosing that 0/0 => 0.

	public Double betweennessCentrality(N node) {
		if(! nodeToBetweenness.containsKey(node)) {
			List<N> nodes = Lists.newArrayList(network.getNodes());
			Double result = 0.0;
			for(Integer i = 0; i < nodes.size(); i++) {
				N node1 = nodes.get(i);
				for(Integer j = i+1; j < nodes.size(); j++) {
					N node2 = nodes.get(j);
					if(! (node.equals(node1) || node.equals(node2))) {
						result += portionOfShortestPathsBetweenAandBthroughN(node1, node2, node);
					}
				}
			}
			nodeToBetweenness.put(node, nodes.size() < 3 ? 0.0 : result * 2 / (nodes.size()-1) / (nodes.size()-2));
		}
		return nodeToBetweenness.get(node);
	}
	private Map<N,Double> nodeToBetweenness = Maps.newHashMap();

	public static Double betweennessCentralityMaxDifference(Integer nodes) {
		return nodes < 3 ? null : (nodes-1)*(nodes-1)*(nodes-2) / 2.0;
	}

	private Double portionOfShortestPathsBetweenAandBthroughN(N a, N b, N n) {
		Integer totalDistance = network.distance(a, b);
		Integer distance1 = network.distance(a, n);
		Integer distance2 = network.distance(b, n);
		if(totalDistance == null || distance1 == null || ! totalDistance.equals(distance1+distance2)) {
			return 0.0;
		}
		Integer totalPaths = numberOfShortestPaths(a,b);
		Integer inclusivePaths = numberOfShortestPaths(a,n)*numberOfShortestPaths(b,n);
		return inclusivePaths * 1.0 / totalPaths;
	}
	private Integer numberOfShortestPaths(N a, N b) {
		Integer distance = network.distance(a,b);
		if(distance == null) {
			return 0;
		}
		if(distance < 1) {
			return 1;
		}
		Integer paths = 0;
		for(N n : network.connections(a)) {
			if(network.distance(n, b) < distance) {
				paths += numberOfShortestPaths(n,b);
			}
		}
		return paths;
	}

	/*
	 * The eigenvector centrality of a node is proportional to the sum
	 * of the eigenvector centralities of its neighbors. I compute
	 * the eigenvector centrality iteratively, using closeness as an
	 * initial guess.

	public Double eigenvectorCentrality(N n) {
		if(eigenvectorCentralities == null) {
			Integer tries = (network.getNodes().size()+5)*(network.getNodes().size()+5);
			Map<N,Double> guess = initialEigenvectorGuess();
			while(true) {
				Map<N,Double> nextGuess = nextEigenvectorGuess(guess);
				if(change(guess,nextGuess) < tinyNum || tries < 0) {
					eigenvectorCentralities = nextGuess;
					return eigenvectorCentrality(n);
				}
				guess = nextGuess;
				tries--;
			}
		}
		return eigenvectorCentralities.get(n) < Math.sqrt(tinyNum) ? 0.0 : eigenvectorCentralities.get(n);
	}
	private Map<N,Double> eigenvectorCentralities = null;

	public static Double eigenvectorCentralityMaxDifference(Integer nodes) {
		return nodes < 3 ? null : (nodes-1)*Math.sqrt(0.5) - Math.sqrt(0.5*(nodes-1));
	}

	private Map<N,Double> nextEigenvectorGuess(Map<N,Double> guess) {
		Map<N,Double> results = Maps.newHashMap();
		for(N node : guess.keySet()) {
			Double result = 0.0;
			for(N neighbor : network.connections(node)) {
				result += guess.get(neighbor);
			}
			results.put(node, result);
		}
		return normalize(results);
	}
	private Double change(Map<N,Double> vec1, Map<N,Double> vec2) {
		Double total = 0.0;
		for(N node : vec1.keySet()) {
			total += Math.abs(vec1.get(node) - vec2.get(node));
		}
		return total;
	}
	private Double tinyNum = 0.0000001;
	private Map<N,Double> normalize(Map<N,Double> vec) {
		Double magnitudeSquared = 0.0;
		for(Double component : vec.values()) {
			magnitudeSquared += component * component;
		}
		Double magnitude = Math.sqrt(magnitudeSquared);
		Double factor = 1 / (magnitude < tinyNum ? tinyNum : magnitude);
		Map<N,Double> normalized = Maps.newHashMap();
		for(N node : vec.keySet()) {
			normalized.put(node, vec.get(node)*factor);
		}
		return normalized;
	}
	private Map<N,Double> initialEigenvectorGuess() {
		Map<N,Double> guess = Maps.newHashMap();
		for(N node : network.getNodes()) {
			guess.put(node, closenessCentrality(node));
		}
		return guess;
	}*/
}