<?php
namespace app\helpers;

use Yii;
use app\models\Alters;
use app\models\Question;
use app\models\Answer;
use app\models\Expression;

class Statistics
{
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
    public $betweenesses = array();
    public $alters = array();
    public $eigenvectorCentralities = array();

    /*
     * Density is the number of connections in the actual
     * network divided by the number of possible connections
     * for networks with that number of nodes.
     */

    public function initComponents($interviewId, $expressionId)
    {
        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId)"))
        ->all();
        if (count($alters) == 0) {
            return false;
        }
        $this->alters = $alters;
        $alters2 = $alters;

        $this->expressionId = $expressionId;
        $this->interviewId = $interviewId;
        $expression = Expression::findOne($expressionId);
        if ($expression->questionId) {
            $expression->question = Question::findOne($expression->questionId);
        }
        $answers = array();
        $answerList = Answer::findAll(array('interviewId'=>$interviewId));
        foreach ($answerList as $answer) {
            if ($answer->alterId1 && $answer->alterId2) {
                $answers[$answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2] = $answer;
            } elseif ($answer->alterId1 && !$answer->alterId2) {
                $answers[$answer->questionId . "-" . $answer->alterId1] = $answer;
            } else {
                $answers[$answer->questionId] = $answer;
            }
        }
        foreach ($alters as $alter) {
            $this->names[$alter->id] = $alter->name;
            $this->betweenesses[$alter->id] = 0;
            array_shift($alters2);
            foreach ($alters2 as $alter2) {
                if ($expression->evalExpression($interviewId, $alter->id, $alter2->id, $answers)) {
                    if (!in_array($alter->id, $this->nodes)) {
                        $this->nodes[] = $alter->id;
                    }
                    if (!in_array($alter2->id, $this->nodes)) {
                        $this->nodes[] = $alter2->id;
                    }
                    $this->adjacencies[] = array($alter->id, $alter2->id);
                    $this->connections[$alter2->id][] = $alter->id;
                    $this->connections[$alter->id][] =  $alter2->id;
                }
            }
        }

        foreach ($alters as $alter) {
            if (!in_array($alter->id, $this->nodes)) {
                $this->isolates[] = $alter->id;
                $this->nodes[] = $alter->id;
                $this->connections[$alter->id] = array();
            }
        }

        foreach ($this->adjacencies as $dyad) {
            if (count($this->components) == 0) {
                $this->components[] = array(0=>$dyad);
                $this->nodeComponents[$dyad[0]] = 0;
                $this->nodeComponents[$dyad[1]] = 0;
                continue;
            } else {
                foreach ($this->components as $index=>&$component) {
                    $inComponent = false;
                    foreach ($component as $cDyad) {
                        if (in_array($dyad[0], $cDyad) || in_array($dyad[1], $cDyad)) {
                            $inComponent = true;
                        }
                    }
                    if ($inComponent) {
                        $this->nodeComponents[$dyad[0]] = $index;
                        $this->nodeComponents[$dyad[1]] = $index;
                        $component[] = $dyad;
                        break;
                    } else {
                        $this->components[] = array(0=>$dyad);
                    }
                }
            }
        }

        foreach ($this->components as $index=>$component) {
            if (count($component) == 1) {
                $this->dyads[] = $component[0];
                unset($this->components[$index]);
            }
        }

        $endNodes = $this->nodes;
        foreach ($this->nodes as $node) {
            array_shift($endNodes);
            foreach ($endNodes as $endNode) {
                $this->getDistance(array($node), $endNode);
            }
        }
        $this->getBetweenesses();
    }

    public function getDistance($visited, $node2)
    {
        $node1 =  $visited[count($visited)-1];
        if (in_array($node2, $this->connections[$node1])) {
            $trail = array_merge($visited, array($node2));
            if (!isset($this->shortPaths[md5($visited[0] . $node2)])) {
                $this->shortPaths[md5($visited[0] . $node2)][] = $trail;
                $this->shortPaths[md5($node2 . $visited[0])][] = $trail;
            } else {
                if (count($trail) < count($this->shortPaths[md5($visited[0] . $node2)][0])) {
                    $this->shortPaths[md5($visited[0] . $node2)] = array();
                    $this->shortPaths[md5($node2 . $visited[0])] = array();
                }

                if (count($this->shortPaths[md5($visited[0] . $node2)]) == 0 || count($trail) == count($this->shortPaths[md5($visited[0] . $node2)][0])) {
                    $this->shortPaths[md5($visited[0] . $node2)][] = $trail;
                    $this->shortPaths[md5($node2 . $visited[0])][] = $trail;
                }
            }
        } else {
            foreach ($this->connections[$node1] as $endNode) {
                if (!in_array($endNode, $visited)) {
                    $v2 = array_merge($visited, array($endNode));
                    if (isset($this->shortPaths[md5($visited[0] . $endNode)])) {
                        if (count($v2) < count($this->shortPaths[md5($visited[0] . $endNode)][0])) {
                            $this->shortPaths[md5($visited[0] . $endNode)] = array();
                            $this->shortPaths[md5($endNode . $visited[0])] = array();
                        }
                        if (count($this->shortPaths[md5($visited[0] . $endNode)]) == 0 || count($v2) == count($this->shortPaths[md5($visited[0] . $endNode)][0])) {
                            $this->shortPaths[md5($visited[0] . $endNode)][] = $v2;
                            $this->shortPaths[md5($endNode . $visited[0])][] = $v2;
                        } else {
                            continue;
                        }
                    } else {
                        $this->shortPaths[md5($visited[0] . $endNode)][] = $v2;
                        $this->shortPaths[md5($endNode . $visited[0])][] = $v2;
                    }
                    $this->getDistance($v2, $node2);
                }
            }
        }
    }

    public function getDegree($alterId)
    {
        if (isset($this->connections[$alterId])) {
            return count($this->connections[$alterId]);
        } else {
            return 0;
        }
    }

    public function getBetweenness($alterId)
    {
        if(isset($this->betweenesses[$alterId]))
            return $this->betweenesses[$alterId];
        else
            return false;
    }

    public function getBetweenesses()
    {
        foreach ($this->shortPaths as $shortPaths) {
            $between = array();
            foreach ($shortPaths as $path) {
                array_shift($path);
                array_pop($path);
                foreach ($path as $node) {
                    if (!isset($between[$node])) {
                        $between[$node] = 1;
                    } else {
                        try {
                            $between[$node] = $between[$node] + 1;
                        } catch (Exception $e) {
                            print_r($between);
                        }
                    }
                }
            }
            foreach ($between as $index=>$value) {
                $this->betweenesses[$index] = $this->betweenesses[$index] + floatval($value/ count($shortPaths));
            }
        }
    }

    private function getPaths($node1, $node2)
    {
        if (isset($this->shortPaths[md5($node1.$node2)])) {
            $key = md5($node1.$node2);
        }
        if (isset($this->shortPaths[md5($node2.$node1)])) {
            $key = md5($node2.$node1);
        }
        if (!isset($key)) {
            return 0;
        }

        $distance = count($this->shortPaths[$key][0]) - 1;
        if ($distance < 1) {
            return 1;
        }
        $paths = 0;
        foreach ($this->connections[$node2] as $n) {
            if ($n == $node1) {
                continue;
            }
            $key2 = '';
            if (isset($this->shortPaths[md5($n.$node2)])) {
                $key2 = md5($n.$node2);
            } elseif (isset($this->shortPaths[md5($node2.$n)])) {
                $key2 = md5($node2.$n);
            } else {
                continue;
            }
            if (count($this->shortPaths[$key2][0]) - 1 < $distance) {
                $paths = $paths + $this->getPaths($n, $node2);
            }
        }
        return $paths;
    }

    public function maxBetweenness()
    {
        $max = array();
        foreach ($this->nodes as $node) {
            $max[] = $this->getBetweenness($node);
        }
        if (count($max) > 0) {
            return max($max);
        } else {
            return false;
        }
    }

    public function minBetweenness()
    {
        $max = array();
        foreach ($this->nodes as $node) {
            $max[] = $this->getBetweenness($node);
        }
        if (count($max) > 0) {
            return min($max);
        } else {
            return false;
        }
    }

    public function maxDegree()
    {
        $max = array();
        if (count($this->nodes) > 0) {
            foreach ($this->nodes as $node) {
                $max[] = count($this->connections[$node]);
            }
            return max($max);
        } else {
            return false;
        }
    }

    public function minDegree()
    {
        $max = array();
        if (count($this->nodes) > 0) {
            foreach ($this->nodes as $node) {
                $max[] = count($this->connections[$node]);
            }
            return min($max);
        } else {
            return false;
        }
    }

    public function getDensity()
    {
        
       /* $possibleEdges = 0;
        for ($i = 0; $i < count($this->alters); $i++) {
            $possibleEdges = $possibleEdges + $i;
        }*/
        $possibleEdges = ( count($this->alters) - 1) *  count($this->alters) / 2;
        $density = count($this->adjacencies) < 1 ? 0.0 : count($this->adjacencies) / $possibleEdges;
        return round($density, 3);
    }

    public function getCloseness($alterId)
    {
        $total = 0;
        $reachable = 0;
        foreach ($this->nodes as $node) {
            if (isset($this->shortPaths[md5($alterId.$node)])) {
                $distance = count($this->shortPaths[md5($alterId.$node)][0]) - 1;
                $total = $total + $distance;
                $reachable++;
            }
        }
        if ($reachable < 1) {
            return 0.0;
        }

        $average = $total / $reachable;
        return $reachable / ($average * (count($this->nodes) - 1));
    }

    public function getClosenesses()
    {
        foreach ($this->alters as $node) {
            $closeness = $this->getCloseness($node->id);
            $this->closenesses[$node->id] = $closeness;
        }
        return $this->closenesses;
    }

    private function nextEigenvectorGuess($guess)
    {
        $results = array();
        foreach ($guess as $gNode=>$value) {
            $result = 0.0;
            if (isset($this->connections[$gNode])) {
                foreach ($this->connections[$gNode] as $neighbor) {
                    $result = $result + $guess[$neighbor];
                }
            }
            $results[$gNode] = $result;
        }
        return $this->normalize($results);
    }

    private $tinyNum = 0.0000001;

    private function normalize($vec)
    {
        $magnitudeSquared = 0.0;
        foreach ($vec as $gNode=>$value) {
            $magnitudeSquared = $magnitudeSquared + pow($value, 2);
        }
        $magnitude =  sqrt($magnitudeSquared);
        $factor = 1 / ($magnitude < $this->tinyNum ? $this->tinyNum : $magnitude);
        $normalized = array();
        foreach ($vec as $gNode=>$value) {
            $normalized[$gNode] = $value  * $factor;
        }
        return $normalized;
    }

    public function eigenvectorCentrality($node = null)
    {
        if (count($this->eigenvectorCentralities) == 0) {
            $tries = (count($this->nodes)+5)*(count($this->nodes)+5);
            $guess = $this->getClosenesses();
            while ($tries >= 0) {
                $nextGuess = $this->nextEigenvectorGuess($guess);
                if ($this->change($guess, $nextGuess) < $this->tinyNum || $tries == 0) {
                    $this->eigenvectorCentralities = $nextGuess;
                }
                $guess = $nextGuess;
                $tries--;
            }
        }
        if ($node != null && isset($this->eigenvectorCentralities[$node])) {
            return $this->eigenvectorCentralities[$node] < sqrt($this->tinyNum) ? 0.0 : round($this->eigenvectorCentralities[$node], 3);
        }
    }

    private function change($vec1, $vec2)
    {
        $total = 0.0;
        foreach ($vec1 as $node=>$value) {
            $total = $total + abs($vec1[$node] - $vec2[$node]);
        }
        return $total;
    }

    public function maxEigenvector()
    {
        $max = array();
        if (count($this->eigenvectorCentralities) == 0) {
            $this->eigenvectorCentrality();
        }
        foreach ($this->eigenvectorCentralities as $node=>$value) {
            $max[] = $value;
        }
        if (count($max) > 0) {
            return round(max($max), 3);
        } else {
            return false;
        }
    }

    public function minEigenvector()
    {
        $max = array();
        if (count($this->eigenvectorCentralities) == 0) {
            $this->eigenvectorCentrality();
        }
        foreach ($this->eigenvectorCentralities as $node=>$value) {
            $max[] = $value;
        }
        if (count($max) > 0) {
            return round(min($max), 3);
        } else {
            return false;
        }
    }

    public function degreeMaxDiff()
    {
        $nodes = count($this->nodes);
        return $nodes < 3 ? null : ($nodes-1) * ($nodes-2);
    }

    public function degreeCentralization()
    {
        $max = $this->maxDegree();
        $total = 0.0;
        foreach ($this->nodes as $node) {
            $total = $total + $max - $this->getDegree($node);
        }
        return count($this->nodes) < 3 ? 0.0 :
            round($total / $this->degreeMaxDiff(), 3);
    }

    public function betweennessMaxDiff()
    {
        $nodes = count($this->nodes);
        return $nodes < 3 ? null : ($nodes-1)*($nodes-1)*($nodes-2) / 2.0;
    }

    public function betweennessCentralization()
    {
        $max = $this->maxBetweenness();
        $total = 0.0;
        foreach ($this->nodes as $node) {
            $total = $total + $max - $this->getBetweenness($node);
        }
        return count($this->nodes) < 3 ? 0.0 :
            round($total / $this->betweennessMaxDiff(), 3);
    }
}
