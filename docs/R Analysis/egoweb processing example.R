##################################################################################
#EGOWEB 2.0 INPUT CODE EXAMPLE                                                   #
#The code below processes data exported from EgoWeb 2.0.                         #
#The two raw data two files it processes are the ego-alter data set and the      #
#alter pair data set.  These files must be from the same study                   #
#with the same number of respondents/egos included in both files.                #
#                                                                                #
#The code will export several .csv files and r data objects to be                #
#saved in an output directory.  The code below requires                          #
#customization for the specific variables in an egoweb project.                  #
#                                                                                #
#The code will process multiple waves of EgoWeb data if they                     #
#are identified at the top of the code. If there are multiple                    #
#waves, they can be identified with names and these names                        #
#are appended to names of the data exported and the R objects                    #
#as necessary.                                                                   #
#                                                                                #
#The files exported are:                                                         #
# NetDescriptives  --  Constructed ego level structrual measures                 #
# NetStats -- Constructed alter level structural measures                        #
# compmeasures -- Constructed ego level composition measures                     #
# ego.raw.constructed.vars  -- Ego variables constructed from raw                #
#                                                                                #
#There are 6 code segments below that refewrence specific variables for one      #
#EgoWeb study that have to be customized for any new study.  The customizatin is #
#limited to changing variable names to match the new study variables or deleting #
#code for processing types of variables that are not in the new study.           #
#                                                                                #
#Each segment is marked with a heading comment starting with "CUSTOMIZATION AREA"#
#and a description of what needs to be customized below the header. There is also#
#a footer with "END OF CUSTOMIZATION AREA" indicating the closing of the area of #
#customization. None of the code should need to be modified in between these     #
#areas of code. The goal is to eventually have this more automatic with an       #
#interface that does not require code modification.                              #
##################################################################################




#################################################################
#CUSTOMIZATION AREA #1:  IDENTIFY LOCATION OF RAW DATA FILES    #
#                        AREA TO STORE PROCESSED FILES          #
#                        NUMBER OF WAVES, (1 OR MORE THAN 1)    #
#                        NAMES OF WAVES, IF ANY.                #
#################################################################

 #set the working directory
 # replace with your project directory
         setwd( 'F://My Documents//Current Projects//R34 Skid Row//EgoWeb Data//Analysis//R' )


 # load the igraph library
         library( igraph)

 # create a directory to hold outputs
         if( !{ 'Outputs' %in% list.files()}){ dir.create( 'Outputs')}


 # list number of waves in the study
 # for single wave studies, list one
         wave.totals <- 2


 #location of data egoweb data
 #this is where you would have downloaded the two .csv files that egoweb exports
 #the ego_alter .csv and the alter-pair .csv files
 data.dir <- "F:/My Documents/Current Projects/R34 Skid Row/EgoWeb Data/"


 #give each wave a name in the order data were collected
 #this string will be appended to file names to keep wave output separate
 #If only one wave, either give some kind of name that will be appended to the file names or use ""
 wave.name = c(
 "Baseline",
 "Follow-up")

 #give each wave a short name in same order as above
 #If only one wave, either give some kind of short name that will be appended to the file names or use ""
 wave.name.short = c(
 "B",
 "F")


 #list files for alter pair data in wave order
 #these are the names of the egoweb alter-pair files that will be processed in this code
 #the order of these files will correspond with the wave names above
 surv.pairs = c(
 w1.pairs <- "F:/My Documents/Current Projects/R34 Skid Row/EgoWeb Data/srht_baseline-alter-pair-data.csv",
 w2.pairs <- "F:/My Documents/Current Projects/R34 Skid Row/EgoWeb Data/srht_followup-alter-pair-data.csv")


 #list files for multilevel composition data in wave order
 #these are the names of the egoweb ego-alter files that will be processed in this code
 #the order of these files will correspond with the wave names above
 surv.comp = c(
 w1.comp <- "F:/My Documents/Current Projects/R34 Skid Row/EgoWeb Data/srht_baseline-ego-alter-data.csv",
 w2.comp <- "F:/My Documents/Current Projects/R34 Skid Row/EgoWeb Data/srht_followup-ego-alter-data.csv")

################################
###END OF CUSTOMIZATION AREA #1#
################################



#this for loop will read in both the composition and structure data exported from EgoWeb
#and creates a list object with both composition and structure data together in one object.
#linked together with an id

for(w in 1:wave.totals) {

        pairs.raw <- read.csv(surv.pairs[w], header=TRUE, sep=",")


        att.raw   <- read.csv(surv.comp[w], header=TRUE, sep=",")

        pairs.raw$Alter.1 <- pairs.raw$Alter.1.Number
        pairs.raw$Alter.2 <- pairs.raw$Alter.2.Number



        #### Note this function links ego and alterpair data set providing a unique id for each ego that is common to both data sets and a unique id for each alter across the dataset.
        link.ego.alters.data<-function(ego,alters,
                               ego.id.field="EgoID",
                               alters.field=c("Alter.1","Alter.2"),
                               ego.id.n.field="ego.id.number"){


        lap<- length(intersect(alters[,ego.id.field], ego[,ego.id.field]))
        la <- length(unique(alters[,ego.id.field]))
        lp <-length(unique(ego[,ego.id.field]))

        if (!(lap==la & lap==lp)){stop("Missing EgoID entries in either dataset")}

        la<- as.data.frame(sort(unique(ego[,ego.id.field])))

        look.up<- cbind(la,1:nrow(la))
        names(look.up) <- c(ego.id.field, ego.id.n.field)

        if(!(ego.id.n.field%in%names(alters))){
                alters<- merge(alters,look.up,by=ego.id.field)}
        if(!(ego.id.n.field%in%names(ego))){
                ego <- merge(ego,look.up,by=ego.id.field)}



        ### change alter names
        alters[,alters.field[1]] <- as.numeric(alters[,alters.field[1]])
        alters[,alters.field[2]] <- as.numeric(alters[,alters.field[2]])

        factor<- 10^ceiling(log10(
                max(alters[,alters.field[1]],alters[,alters.field[2]])
        ))


        alters[,alters.field[1]] <- factor*alters[,ego.id.n.field]+alters[,alters.field[1]]
        alters[,alters.field[2]] <- factor*alters[,ego.id.n.field]+alters[,alters.field[2]]

        return(list(ego=ego,alters=alters))

        }




        #### This is the list object with linked composition and structure data
        linked.egocentric.data <- link.ego.alters.data(ego=att.raw,
                                               alters=pairs.raw,
                                               ego.id.field="EgoID",
                                               alters.field=c("Alter.1","Alter.2"),
                                               ego.id.n.field="n")



###################################################################
#CUSTOMIZATION AREA #2:   IDENTIFY ALTER PAIR VARIABLE THAT       #
#                         CAN BE DICHOTOMIZED 3 WAYS TO CALCULATE #
#                         NETWORK STATS FOR WEAKLY TIED NETWORKS, #
#                         STRONGLY TIED NETWORKS, AND NETWORKS    #
#                         AND NETWORKS IN THE MIDDLE. THIS SETS   #
#                         UP NETWORK STATS CALCULATIONS AND       #
#                         VISUALIZATION ROUTINES BELOW.           #
#                         THE ONLY THING THAT NEEDS TO BE CHANGED #
#                         BELOW IS THE NAME OF THE ALTER PAIR     #
#                         VARIABLE DETERMINING THE RELATIONSHIP   #
#                         TIE STRENGTH.                           #
###################################################################


#the code below produces composition and strucuture analyses for each wave of data and exports files
#with constructed variables as well as a pdf with displays of 3 different versions of the same egocentric
#networks


        ### this imports import data from the object created above into separate pairs and alter atribute files
        pairs <- (linked.egocentric.data[[2]])
        att <- (linked.egocentric.data[[1]])


        ### this dichotomizes edges from egocetric pair data into three levels:  weak, medium, and strong
        ### based on an alter pair question.  This example uses the variable "STRUCT1"
        ### Multiple alter pair questions will require more code to define the levels
        ### weak = any type of connection = 1, others = 0
        ### strong = only the strongest relationship tie = 1, others = 0
        ### medium = any relationship tie above a level stronger than weak defined by the researcher = 1, others = 0
        pairs$weak[pairs$STRUCT1<0]  <- 0
        pairs$weak[pairs$STRUCT1==1] <- 0
        pairs$weak[pairs$STRUCT1==2] <- 1
        pairs$weak[pairs$STRUCT1==3] <- 1
        pairs$weak[pairs$STRUCT1==4] <- 1

        pairs$medium[pairs$STRUCT1<0]  <- 0
        pairs$medium[pairs$STRUCT1==1] <- 0
        pairs$medium[pairs$STRUCT1==2] <- 0
        pairs$medium[pairs$STRUCT1==3] <- 1
        pairs$medium[pairs$STRUCT1==4] <- 1

        pairs$strong[pairs$STRUCT1<0]  <- 0
        pairs$strong[pairs$STRUCT1==1] <- 0
        pairs$strong[pairs$STRUCT1==2] <- 0
        pairs$strong[pairs$STRUCT1==3] <- 0
        pairs$strong[pairs$STRUCT1==4] <- 1


################################
###END OF CUSTOMIZATION AREA #2#
################################



        ### this code calculate some id attributes (i.e. number of unique respondents based on different id values
        unique.respondent.ids <- unique( pairs[,1])
        unique.n.ids <- unique( pairs[,10])
        unique.egoweb.ids <- unique( pairs[,1])
        unique.interview.ids <- unique( pairs[,2])
        length.unique.respondent.ids <- length( unique.respondent.ids)

        #### this constructs igraph network objects ----------------------------------------

        ### Alter.1.number and Alter.2.number are unique node identifiers for a pair of alters for one ego numbered 1 to the total number of

        IgraphMaker <- function( dat, variable){
                kMax <- max( dat$Alter.2.Number)
                ego <- c( dat$Alter.1.Number, dat$Alter.2.Number, 1:kMax )
                alt <- c( dat$Alter.2.Number, dat$Alter.1.Number, 1:kMax )
                val <- c( rep( dat[ , variable], 2), rep(0, kMax) )
                display <- tapply( val, list( ego, alt), max)
                display[ is.na( display)] <- 0
                graph.adjacency( display, mode= 'undirected', diag= F)
        }





        ###  n is a consecutive number from 1 to the totoal number of egos in the data set
        ###  "weak", "medium", and "strong" are dichotomous variables constucted from the EgoWeb alter-pair relationship strength variable measure
        ###  "weak" is the most inclusive level including all ties.  For example, if the alter pair question is about amount of contact, and the options are "rarely", "sometimes" and "often", "weak" would include all three.
        ###  "medium" is a middle value of the alter-pair variable (if there is a middle value)e.  For example, if the alter-pair variable is contact, it would include "sometimes" and "often" but not "rarely"
        ###  "strong" is the most stringent definition of a relationship based on the alter-paiar variable. If the alter-pair variable is contact, it would only be "often" and not "sometimes" or "rarely"


        weak.matrix <- by( pairs, pairs$n, IgraphMaker, variable= 'weak')
        medium.matrix <- by( pairs, pairs$n, IgraphMaker, variable= 'medium')
        strong.matrix <- by( pairs, pairs$n, IgraphMaker, variable= 'strong')


        f <- function(x,y){
                z <- get.adjacency( x) * get.adjacency( y)
                z <- graph.adjacency( z, mode= 'undirected', diag= F)
                z
        }



        #### CONSTRUCT CENTRALITY MEASURES FOR DIFFERNT LEVELS OF TIE STRENGTH AMONG ALTERS
        #### DIFFERENT VALUES ARE CALCULATED FOR STRONG, MEDIUM, AND WEAK

        # function to extract the vector from the eigenvector centrality function
        EvWrapper <- function( x){ evcent( x)$vector}

        ### This calculates three *alter-level* centrality measures (degree centrality, eigenvector centrality, betweenness centrality)
        ### for 3 different dichotomized alter pair matricies
        ### alter.net.stats is initialized as a matrix with 9 columns (3 centralities x 3 matricies)
        alter.net.stats<-matrix( nrow= nrow(att), ncol=9)
        colnames(alter.net.stats) <- c("degree_w", "degree_m", "degree_s", "eigenvector_w",
                               "eigenvector_m","eigenvector_s", "betweenness_w", "betweenness_m","betweenness_s")

        alter.net.stats[,1] <- unlist( lapply( weak.matrix, degree))
        alter.net.stats[,2] <- unlist( lapply( medium.matrix, degree))
        alter.net.stats[,3] <- unlist( lapply( strong.matrix, degree))
        alter.net.stats[,4] <- unlist( lapply( weak.matrix, EvWrapper))
        alter.net.stats[,5] <- unlist( lapply( medium.matrix, EvWrapper))
        alter.net.stats[,6] <- unlist( lapply( strong.matrix, EvWrapper))
        alter.net.stats[,7] <- unlist( lapply( weak.matrix, betweenness))
        alter.net.stats[,8] <- unlist( lapply( medium.matrix, betweenness))
        alter.net.stats[,9] <- unlist( lapply( strong.matrix, betweenness))


        #### This calculates *overall* network stats for each matrix.
        #### 9 variables are calculated: density, components, transitivity, degree centralization, betweenness centralization,
        #### effective size, diameter, shortest.path, and weak brokerage.
        #### netdescriptives is a matrix initialized with 29 columns:  3 matrices x 9 variables (27) + 2 id variables
        #### ID variables uniquely identify interview id (EgoID) and interview number on EgoWeb database (Interview_ID)

        netdescriptives <- matrix(nrow=length.unique.respondent.ids, ncol=29, byrow=F)
        rownames(netdescriptives) <- unique.n.ids
        colnames(netdescriptives) <- c('Interview_ID','EgoID','density_weak', 'density_medium',
                               'density_strong', 'components_weak', 'components_medium',
                               'components_strong', 'transitivity_weak', 'transitivity_medium', 'transitivity_strong',
                                'deg.centralization_weak','deg.centralization_medium', 'deg.centralization_strong',
                                'bet.centralization_weak','bet.centralization_medium', 'bet.centralization_strong',
                                'eff.size_weak','eff.size_medium', 'eff.size_strong',
                                'diameter_weak','diameter_medium', 'diameter_strong',
                                'shortest.path_weak','shortest.path_medium', 'shortest.path_strong',
                                'brokerage_weak','brokerage_medium', 'brokerage_strong')

        # add some unique egolevel ids to the data set

        netdescriptives[,1] <- unique.interview.ids
        netdescriptives[,2] <- as.vector(unique.egoweb.ids)

        # calculate network density
        netdescriptives[,3] <- unlist( lapply( weak.matrix, graph.density))
        netdescriptives[,4] <- unlist( lapply( medium.matrix, graph.density))
        netdescriptives[,5] <- unlist( lapply( strong.matrix, graph.density))


        # ennumerate and extract number of clusters
        ClusterWrapper <- function( x){ clusters( x, mode= 'strong')$no }

        # ennumerate and extract transitivity
        TransitivityWrapper <- function( x){ transitivity( x, type=c("global"))[1] }


        # ennumerate and extract degree centralization
        DegcentralizationWrapper <- function( x){ centralization.degree( x)$centralization }

        # ennumerate and extract betweenness centralization
        BetcentralizationWrapper <- function( x){ centralization.betweenness( x)$centralization }


        # ennumerate and extract diameter
        DiameterWrapper <- function( x){ diameter( x) }


        # ennumerate and extract Effective size
        EffectiveSize <- function( x) {
                e <- ecount(x)
                v <- vcount(x)
                v - 2*e / v
        }




        # ennumerate and extract Aveage Shortest Path
        ShortestPathWrapper <- function( x){
                sp <- shortest.paths( x)
                msp <- mean(sp[is.finite(sp)])

        }



        # ennumerate and extract Brokerage
        BrokerageWrapper <- function( x){
                v <- vcount( x)
                e <- ecount( x)
                pe <- v*(v-1)/2
                ne <- pe - e
                br <- ne / pe

        }





        ### This pastes each of the above calculated variables into a column in the netdescriptive matrix
        netdescriptives[,6] <- unlist( lapply( weak.matrix, ClusterWrapper))
        netdescriptives[,7] <- unlist( lapply( medium.matrix, ClusterWrapper))
        netdescriptives[,8] <- unlist( lapply( strong.matrix, ClusterWrapper))

        netdescriptives[,9] <- unlist( lapply( weak.matrix, TransitivityWrapper))
        netdescriptives[,10] <- unlist( lapply( medium.matrix, TransitivityWrapper))
        netdescriptives[,11] <- unlist( lapply( strong.matrix, TransitivityWrapper))


        netdescriptives[,12] <- unlist( lapply( weak.matrix, DegcentralizationWrapper))
        netdescriptives[,13] <- unlist( lapply( medium.matrix, DegcentralizationWrapper))
        netdescriptives[,14] <- unlist( lapply( strong.matrix, DegcentralizationWrapper))

        netdescriptives[,15] <- unlist( lapply( weak.matrix, BetcentralizationWrapper))
        netdescriptives[,16] <- unlist( lapply( medium.matrix, BetcentralizationWrapper))
        netdescriptives[,17] <- unlist( lapply( strong.matrix, BetcentralizationWrapper))

        netdescriptives[,18] <- unlist( lapply( weak.matrix, EffectiveSize))
        netdescriptives[,19] <- unlist( lapply( medium.matrix, EffectiveSize))
        netdescriptives[,20] <- unlist( lapply( strong.matrix, EffectiveSize))

        netdescriptives[,21] <- unlist( lapply( weak.matrix, DiameterWrapper))
        netdescriptives[,22] <- unlist( lapply( medium.matrix, DiameterWrapper))
        netdescriptives[,23] <- unlist( lapply( strong.matrix, DiameterWrapper))


        netdescriptives[,24] <- unlist( lapply( weak.matrix, ShortestPathWrapper))
        netdescriptives[,25] <- unlist( lapply( medium.matrix, ShortestPathWrapper))
        netdescriptives[,26] <- unlist( lapply( strong.matrix, ShortestPathWrapper))


        netdescriptives[,27] <- unlist( lapply( weak.matrix, BrokerageWrapper))
        netdescriptives[,28] <- unlist( lapply( medium.matrix, BrokerageWrapper))
        netdescriptives[,29] <- unlist( lapply( strong.matrix, BrokerageWrapper))



        ### this exports the results into the output directory
        write.csv(netdescriptives, file=paste('Outputs/NetDescriptives',wave.name[w],".csv",sep=""))




#####################################################################
#CUSTOMIZATION AREA #3:  THE CODE BELOW PRODUCES A PDF WITH 3       #
#                        VISUALIZATIONS OF EACH NETWORK ON ONE PAGE #
#                        ALONG WITH SOME STATS AND ID INFORMATION.  #
#                        THE ONLY VARIABLES NEEDING TO BE CHANGED   #
#                        BELOW ARE THE VARIABLE USED TO DETERMINE   #
#                        SHAPE OF NODES AND COLOR. EVERYTHING ELSE  #
#                        SHOULD BE AUTOMATIC.                       #
#####################################################################



        #### This Visualizes networks ----------------------------------------


        ### this preps the visualization with some calculations
        ### the shape of the nodes is defined below.  This example uses the variable "COMP2" to define the shape
        ### shapes are square, circle, and rectangle
        ### these are assigned to values of 1, 2 and 3 of the "COMP2" variable

        att$shape <- 3
        att$shape[att$COMP2==1] <- 1
        att$shape[att$COMP2==2] <- 2

        att$shape <- c( 'square', 'circle', 'rectangle' )[ att$shape]


        ### this preps the visualization with some calculations
        ### the color of the nodes is defined below.  This example uses the variable "COMP7" to define the color
        ### theree different colors will be are assigned to values of 1, 2 and 3 of the "COMP27 variable


        att$color[att$COMP7==0] <- 1
        att$color[att$COMP7==1] <- 2
        att$color[att$COMP7==2] <- 3


################################
###END OF CUSTOMIZATION AREA #3#
################################




        ### this defines some objects that will be used in the graph functions below
        frame.color <- hsv( h= 0.75, s= c( 1, 0.2, 0.6), v= c(0.1, 0.3, 0.5))[ att$color]
        att <- data.frame( att, 'frame.color'= frame.color, stringsAsFactors= F)
        att$color <- hsv( h= 0.75, s= c( 1, 0.2, 0.6), v= c(0.2, 0.6, 1))[ att$color] #BWG

        oneatt <- by( att[,c('color', 'shape', 'frame.color')],
              att$n, identity )

        n <- unique( att[,c('n','EgoID')])$EgoID

        names( n) <- unique( att[,c('n','EgoID')])$n


        ### This calculates the common network layout to be used for weak/medium/strong
        common.layout <- lapply( weak.matrix, layout.fruchterman.reingold)


        ### This renders the networks on six panel, 7in x 5in pdf pages, one per interview
        ### and prints these networks on one page of a pdf for each interview
        ### IDs and the wave variables created at the top of this script will identify the
        ### unique interview.  Also some network stats will be printed on the pages
        pdf(paste("Outputs/SRHTgraphs",wave.name[w],".pdf",sep=""), width= 7, height= 5)

        par( mar= c(0,0,1,0), mfrow= c(2,3))

        for( iter in 1:length.unique.respondent.ids){

          # render title in the top left panel
          plot.new()
          plot.window( xlim= 0:1, ylim= 0:1)
          text( 0.5, 0.5,
                labels= paste('Social Network\nOf SRHT-',wave.name.short[w],'respondent\n#', n[iter], sep= ''
                ))

          # print network stats for the three graphs in the top middle panel
          plot.new()
          plot.window( xlim= 0:1, ylim= 0:1)
          text( .1, .3,
                labels= paste( '*Weak* Density:      ', round(as.numeric(netdescriptives[iter,3]), digits = 2), sep= ' '
                ), adj = c(0,0))
          text( 0.1, 0.2,
                labels= paste( '*Medium* Density:', round(as.numeric(netdescriptives[iter,4]), digits = 2), sep= ' '
                ), adj = c(0,0))
          text( 0.1, 0.1,
                labels= paste( '*Strong* Density:', round(as.numeric(netdescriptives[iter,5]), digits = 2), sep= ' '
                ), adj = c(0,0))

          # render all "weak" ties in the top right panel
          plot.igraph( weak.matrix[[iter]], main= 'Weak',
                       vertex.size= ifelse( oneatt[[iter]]$shape == 'circle', 8, 4),
                       vertex.label= '', vertex.color= oneatt[[iter]]$color,
                       vertex.frame.color= oneatt[[iter]]$frame.color,
                       vertex.shape= oneatt[[iter]]$shape, edge.color= 'gray75',
                       frame = "true",
                       layout= common.layout[[iter]]
          )

          # render legend in the lower left panel, need to add shape for male and female
          plot.new()
          plot.window( xlim= 0:1, ylim= 0:1)
          legend( "center",
                  fill= hsv( h= 0.75, s= c( 1, 0.2, 0.6), v= c(0.2, 1, 0.6))[c(2,1,3)],
                  border= hsv( h= 0.75, s= c( 1, 0.2, 0.6), v= c(0.1, 0.5, 0.3))[c(2,1,3)],
                  legend=c("No Drink", "Past Drink", "Recent Drink"),
                  bty= 'n'
          )
          legend( "bottom", col=c("black", "black"),
                  legend=c("male alter", "female alter"), pch=c(21, 22),
                  bty= 'n'
          )

          # render "medium" network in the lower middle panel
          #index <- medium.matrix[[iter]]
          #if( sum(index) > 0){
          plot.igraph( medium.matrix[[iter]], main = 'Medium',
                       vertex.size= ifelse( oneatt[[iter]]$shape == 'circle', 8, 4),
                       vertex.label= '', vertex.color= oneatt[[iter]]$color,
                       vertex.frame.color= oneatt[[iter]]$frame.color,
                       vertex.shape= oneatt[[iter]]$shape, edge.color= 'gray75',
                       frame = "true",
                       # layout= common.layout[[iter]]
                       layout= layout.fruchterman.reingold
          )

          # render "strong" network
          #index <- strong.matrix[[iter]]
          #if( sum(index) > 0){
          plot.igraph( strong.matrix[[iter]], main = 'Strong',
                       vertex.size= ifelse( oneatt[[iter]]$shape == 'circle', 8, 4),
                       vertex.label= '', vertex.color= oneatt[[iter]]$color,
                       vertex.frame.color= oneatt[[iter]]$frame.color,
                       vertex.shape= oneatt[[iter]]$shape, edge.color= 'gray75',
                       frame = "true",
                       # layout= common.layout[[iter]]
                       layout= layout.fruchterman.reingold
          )

        }



        graphics.off()



#########################################################################
#CUSTOMIZATION AREA #4:  THE CODE BELOW PRODUCES NETWORK COMPOSITION    #
#                        VARIABLES FROM RAW EGOWEB DATA. THE VARIABLES  #
#                        CONSTRUCTED BELOW ARE AT THE ALTER LEVEL BUT   #
#                        WILL ENABLE CREATION OF SUMMARY NETWORK        #
#                        COMPOSITION VARIABLES AT THE EGO LEVEL, SUCH AS#
#                        PROPORTIONS AND OVERALL AVERAGES ACROSS ALTERS.#
#########################################################################




        ### This section below calculates composition variables for each interview and adds them to the alter level data set (att) with raw egoweb variables
        ### Each calculation will have to be customized for types and names of EgoWeb alter variables


        # load the plyr package
        # this package is usesful for taking constructed and raw variables and calculating summary composition variables for each ego
        library(plyr)


        ### This initializes two variables from a dichotomous variable

                att$sex_male                                            <- 0
                att$sex_female                                          <- 0
                att$sex_other                                           <- 0
                att$sex_male[att$COMP2==1]                              <- 1
                att$sex_female[att$COMP2==2]                            <- 1
                att$sex_other[att$COMP2==3]                             <- 1


        ### This initializes three variables from a 3 level variable

                att$homeless.ever                                       <- 0
                att$homeless.in.past                                    <- 0
                att$homeless.recent                                     <- 0
                att$homeless.ever[att$COMP3>0]                          <- 1
                att$homeless.in.past[att$COMP3==1]                      <- 1
                att$homeless.recent[att$COMP3==3]                       <- 1


        ### This initializes three variables from a 3 level variable

                att$interact.rarely                                     <- 0
                att$interact.sometimes                                  <- 0
                att$interact.often                                      <- 0
                att$interact.rarely[att$COMP5a==1]                      <- 1
                att$interact.sometimes[att$COMP5a==2]                   <- 1
                att$interact.often[att$COMP5a==3]                       <- 1



        ### This converts an ordinal variables with 9 levels into one quantitative variable

                att$contact.days                                        <- 0
                att$contact.days[att$COMP5b==0]                         <- 0
                att$contact.days[att$COMP5b==1]                         <- 1
                att$contact.days[att$COMP5b==2]                         <- 2
                att$contact.days[att$COMP5b==3]                         <- 3
                att$contact.days[att$COMP5b==4]                         <- 4
                att$contact.days[att$COMP5b==5]                         <- 8
                att$contact.days[att$COMP5b==6]                         <- 14
                att$contact.days[att$COMP5b==7]                         <- 22
                att$contact.days[att$COMP5b==8]                         <- 28


        ### This initializes two dichotomous variables from a 3 level ordinal variable

                att$sex.with                                            <- 0
                att$sex.with.recent                                     <- 0

                att$sex.with[att$COMP6==0]                              <- 0
                att$sex.with[att$COMP6==1]                              <- 1
                att$sex.with[att$COMP6==2]                              <- 1

                att$sex.with.recent[att$COMP6==0]                       <- 0
                att$sex.with.recent[att$COMP6==2]                       <- 0
                att$sex.with.recent[att$COMP6==2]                       <- 1



        ### This initializes a percenet variable from from a 5 level ordinal variable

                att$sex.with.upsex                                      <- 0

                att$sex.with.upsex[att$COMP6==0]                        <- 0
                att$sex.with.upsex[att$COMP6b==0]                       <- 0
                att$sex.with.upsex[att$COMP6b==1]                       <- .25
                att$sex.with.upsex[att$COMP6b==2]                       <- .5
                att$sex.with.upsex[att$COMP6b==3]                       <- .75
                att$sex.with.upsex[att$COMP6b==4]                       <- 1.00


                att$any.upsex                                           <- 0

                att$any.upsex[att$COMP6b==0]                            <- 0
                att$any.upsex[att$COMP6b==1]                            <- 1
                att$any.upsex[att$COMP6b==2]                            <- 1
                att$any.upsex[att$COMP6b==3]                            <- 1
                att$any.upsex[att$COMP6b==4]                            <- 1



        ### This initializes a dichomous variables from a 3 level variable

                att$drink.with                                          <- 0

                att$drink.with[att$COMP7==0]                            <- 0
                att$drink.with[att$COMP7==1]                            <- 1
                att$drink.with[att$COMP7==2]                            <- 1


        ### This initializes a dichomous variables from a 3 level variable


                att$drink.with.recent                                   <- 0

                att$drink.with.recent[att$COMP7==0]                     <- 0
                att$drink.with.recent[att$COMP7==1]                     <- 0
                att$drink.with.recent[att$COMP7==2]                     <- 1

        ### This initializes a dichomous variables from a 3 level variable

                att$drugs.with                                          <- 0

                att$drugs.with[att$COMP8==0]                            <- 0
                att$drugs.with[att$COMP8==1]                            <- 1
                att$drugs.with[att$COMP8==2]                            <- 1

        ### This initializes a dichomous variables from a 3 level variable

                att$drugs.with.recent                                   <- 0

                att$drugs.with.recent[att$COMP8==0]                     <- 0
                att$drugs.with.recent[att$COMP8==1]                     <- 0
                att$drugs.with.recent[att$COMP8==2]                     <- 1

        ### This initializes a dichomous variables from a 3 level variable

                att$aod.more.with                                       <- 0

                att$aod.more.with[att$COMP9==0]                         <- 0
                att$aod.more.with[att$COMP9==1]                         <- 1
                att$aod.more.with[att$COMP9==2]                         <- 1

        ### This initializes a dichomous variables from a 3 level variable

                att$aod.more.with.recent                                <- 0

                att$aod.more.with.recent[att$COMP9==0]                  <- 0
                att$aod.more.with.recent[att$COMP9==1]                  <- 0
                att$aod.more.with.recent[att$COMP9==2]                  <- 1

        ### This initializes a dichomous variables from 3 other constructed variables

                att$any.risk                                            <- 0

                att$any.risk[att$drink.with==1]                         <- 1
                att$any.risk[att$drugs.with==1]                         <- 1
                att$any.risk[att$aod.more.with==1]                      <- 1


        ### This initializes a variable that is a count of several dichotomous variables


                att$sum.risk                                            <- 0
                att$sum.risk                                            <-  att$drink.with+ att$drugs.with + att$aod.more.with


        ### This initializes a dichomous variables from 3 other constructed variables

                att$any.risk.recent                                     <- 0

                att$any.risk.recent[att$drugs.with.recent==1]           <- 1
                att$any.risk.recent[att$drink.with.recent==1]           <- 1
                att$any.risk.recent[att$aod.more.with.recent==1]        <- 1

        ### This initializes a variable that is a count of several dichotomous variables

                att$sum.risk.recent                                     <- 0
                att$sum.risk.recent                                     <-  att$drugs.with.recent + att$drink.with.recent + att$aod.more.with.recent


        ### This initializes a dichomous variables from a 3 level variable

                att$em.support                                          <- 0

                att$em.support[att$COMP10==0]                           <- 0
                att$em.support[att$COMP10==1]                           <- 1
                att$em.support[att$COMP10==2]                           <- 1

        ### This initializes a dichomous variables from a 3 level variable

                att$em.support.recent                                   <- 0

                att$em.support.recent[att$COMP10==0]                    <- 0
                att$em.support.recent[att$COMP10==1]                    <- 0
                att$em.support.recent[att$COMP10==2]                    <- 1


        ### This initializes a dichomous variables from a 3 level variable

                att$info.support                                        <- 0

                att$info.support[att$COMP11==0]                         <- 0
                att$info.support[att$COMP11==1]                         <- 1
                att$info.support[att$COMP11==2]                         <- 1

        ### This initializes a dichomous variables from a 3 level variable


                att$info.support.recent                                 <- 0

                att$info.support.recent[att$COMP11==0]                  <- 0
                att$info.support.recent[att$COMP11==1]                  <- 0
                att$info.support.recent[att$COMP11==2]                  <- 1

        ### This initializes a dichomous variables from a 3 level variable


                att$tan.support                                         <- 0

                att$tan.support[att$COMP12==0]                          <- 0
                att$tan.support[att$COMP12==1]                          <- 1
                att$tan.support[att$COMP12==2]                          <- 1

        ### This initializes a dichomous variables from a 3 level variable


                att$tan.support.recent                                  <- 0

                att$tan.support.recent[att$COMP12==0]                   <- 0
                att$tan.support.recent[att$COMP12==1]                   <- 0
                att$tan.support.recent[att$COMP12==2]                   <- 1

        ### This initializes a dichomous variables from 3 other constructed variables


                att$any.support                                         <- 0

                att$any.support[att$em.support==1]                      <- 1
                att$any.support[att$info.support==1]                    <- 1
                att$any.support[att$tan.support==1]                     <- 1

        ### This initializes a variable that is a count of several dichotomous variables

                att$sum.support                                         <- 0
                att$sum.support                                         <- att$tan.support + att$em.support + att$info.support




        ### This initializes a dichomous variables from 3 other constructed variables


                att$any.support.recent                                  <- 0

                att$any.support.recent[att$em.support.recent==1]        <- 1
                att$any.support.recent[att$info.support.recent==1]      <- 1
                att$any.support.recent[att$tan.support.recent==1]       <- 1

        ### This initializes a variable that is a count of several dichotomous variables

                att$sum.support.recent                                  <- 0
                att$sum.support.recent                                  <- att$tan.support.recent + att$em.support.recent + att$info.support.recent


        ### This initializes three dichotomous variables that are combinations of two constructed dichotomous variables

                att$risk.and.support                                            <- 0
                att$risk.not.support                                            <- 0
                att$support.not.risk                                            <- 0

                att$risk.and.support[att$any.support==1 & att$any.risk==1]     <- 1
                att$risk.not.support[att$any.support==0 & att$any.risk==1]     <- 1
                att$support.not.risk[att$any.support==1 & att$any.risk==0]     <- 1


        ### This initializes three dichotomous variables that are combinations of two constructed dichotomous variables

                att$risk.and.support.recent                                                             <- 0
                att$risk.not.support.recent                                                             <- 0
                att$support.not.risk.recent                                                             <- 0

                att$risk.and.support.recent[att$any.support.recent==1 & att$any.risk.recent==1]     <- 1
                att$risk.not.support.recent[att$any.support.recent==0 & att$any.risk.recent==1]     <- 1
                att$support.not.risk.recent[att$any.support.recent==1 & att$any.risk.recent==0]     <- 1


        ### This initializes a variable with a different, more descriptive name but the same levels as the original variable (without missing values such as "don't know" or "refuse")

                att$negative.rel                                        <- 0

                att$negative.rel[att$COMP14==0]                         <- 0
                att$negative.rel[att$COMP14==1]                         <- 1
                att$negative.rel[att$COMP14==2]                         <- 2
                att$negative.rel[att$COMP14==3]                         <- 3


        ### This initializes 4 dichotomous variables from from a 3 level variable


                att$time.spend.more                                     <- 0
                att$time.spend.more.or.same                             <- 0
                att$time.spend.less                                     <- 0
                att$time.spend.same                                     <- 0

                att$time.spend.more[att$COMP15==1]                      <- 1
                att$time.spend.more[att$COMP15==2]                      <- 0
                att$time.spend.more[att$COMP15==3]                      <- 0

                att$time.spend.less[att$COMP15==1]                      <- 0
                att$time.spend.less[att$COMP15==2]                      <- 0
                att$time.spend.less[att$COMP15==3]                      <- 1

                att$time.spend.same[att$COMP15==1]                      <- 0
                att$time.spend.same[att$COMP15==2]                      <- 1
                att$time.spend.same[att$COMP15==3]                      <- 0

                att$time.spend.more.or.same[att$time.spend.more==1]     <- 1
                att$time.spend.more.or.same[att$time.spend.same==1]     <- 1


################################
###END OF CUSTOMIZATION AREA #4#
################################


        # this combines all alter level constructed variables (structural and composition) with attributes raw data input
        # the end result is one alter level data set with raw + structure + constructed composition variables
                all.alter.constructed.variables <- cbind( att, alter.net.stats)


        #this constructs some additional variables that are combinations of the constructed composition and structure variables for each alter
        #the variables are degree centrality for certain types of alters and will be averaged for egos later in the code to provide measures of how
        #central certain types of alters are in the ego networks


                all.alter.constructed.variables$risk.degree_w               <- all.alter.constructed.variables$degree_w * all.alter.constructed.variables$any.risk
                all.alter.constructed.variables$risk.degree_m               <- all.alter.constructed.variables$degree_m * all.alter.constructed.variables$any.risk
                all.alter.constructed.variables$risk.degree_s               <- all.alter.constructed.variables$degree_s * all.alter.constructed.variables$any.risk

                all.alter.constructed.variables$risk.recent.degree_w        <- all.alter.constructed.variables$degree_w * all.alter.constructed.variables$any.risk.recent
                all.alter.constructed.variables$risk.recent.degree_m        <- all.alter.constructed.variables$degree_m * all.alter.constructed.variables$any.risk.recent
                all.alter.constructed.variables$risk.recent.degree_s        <- all.alter.constructed.variables$degree_s * all.alter.constructed.variables$any.risk.recent

                all.alter.constructed.variables$support.degree_w            <- all.alter.constructed.variables$degree_w * all.alter.constructed.variables$any.support
                all.alter.constructed.variables$support.degree_m            <- all.alter.constructed.variables$degree_m * all.alter.constructed.variables$any.support
                all.alter.constructed.variables$support.degree_s            <- all.alter.constructed.variables$degree_s * all.alter.constructed.variables$any.support

                all.alter.constructed.variables$support.recent.degree_w     <- all.alter.constructed.variables$degree_w * all.alter.constructed.variables$any.support.recent
                all.alter.constructed.variables$support.recent.degree_m     <- all.alter.constructed.variables$degree_m * all.alter.constructed.variables$any.support.recent
                all.alter.constructed.variables$support.recent.degree_s     <- all.alter.constructed.variables$degree_s * all.alter.constructed.variables$any.support.recent


        #this exports all of the data at the alter level: all alter level constructed v(composition, structure, composition + structure) ariables
        write.csv( all.alter.constructed.variables, file=paste('Outputs/NetStats',wave.name[w],'.csv',sep=""), row.names= F)



###########################################################################
#CUSTOMIZATION AREA #5:  THE CODE BELOW TAKES THE ALTER LEVEL COMPOSITION #
#                        VARIABLES CREATED IN CUSTOMIZATION AREA 4 AND    #
#                        RUNS A FUNCTION THAT PRODUCES SUMMARY VARIABLES  #
#                        FOR EGOS.                                        #
###########################################################################


        #This function creates a vector of calculations that will be made at the ego level. The function is called in another function below
        #The function calculates absolute frequencies of relevant categories for some variables and sums of opther variables
        #These variables will be divided by the total number of alters for each ego.
        #The absolute count variables divided by number of alters will produce proportions at the ego level.
        #The sums variables will produce averages
        #Some variables will be divided by counts of types of alters rather than all alters depending on the type of variable

                comp.fun <- function(df) {
                x <- c(sum(df$sex_male==1),
                       sum(df$sex_female==1),
                       sum(df$sex_other==1),
                       sum(df$homeless.ever==1),
                       sum(df$homeless.in.past==1),
                       sum(df$homeless.recent==1),
                       sum(df$interact.rarely==1),
                       sum(df$interact.sometimes==1),
                       sum(df$interact.often==1),
                       sum(df$contact.days),
                       sum(df$sex.with==1),
                       sum(df$sex.with.recent==1),
                       sum(df$sex.with.upsex==1),
                       sum(df$any.upsex==1),
                       sum(df$drink.with==1),
                       sum(df$drink.with.recent==1),
                       sum(df$drugs.with==1),
                       sum(df$drugs.with.recent==1),
                       sum(df$aod.more.with==1),
                       sum(df$aod.more.with.recent==1),
                       sum(df$any.risk==1),
                       sum(df$any.risk.recent==1),
                       sum(df$em.support==1),
                       sum(df$em.support.recent==1),
                       sum(df$info.support==1),
                       sum(df$info.support.recent==1),
                       sum(df$tan.support==1),
                       sum(df$tan.support.recent==1),
                       sum(df$any.support==1),
                       sum(df$any.support.recent==1),
                       sum(df$negative.rel==1),
                       sum(df$time.spend.more==1),
                       sum(df$time.spend.more.or.same==1),
                       sum(df$time.spend.less==1),
                       sum(df$time.spend.same==1),
                       sum(df$sum.risk),
                       sum(df$sum.support),
                       sum(df$sum.risk.recent),
                       sum(df$sum.support.recent),
                       sum(df$risk.and.support),
                       sum(df$risk.not.support),
                       sum(df$support.not.risk),
                       sum(df$risk.and.support.recent),
                       sum(df$risk.not.support.recent),
                       sum(df$support.not.risk.recent),
                       sum(df$risk.degree_w),
                       sum(df$risk.degree_m),
                       sum(df$risk.degree_s),
                       sum(df$risk.recent.degree_w),
                       sum(df$risk.recent.degree_m),
                       sum(df$risk.recent.degree_s),
                       sum(df$support.degree_w),
                       sum(df$support.degree_m),
                       sum(df$support.degree_s),
                       sum(df$support.recent.degree_w),
                       sum(df$support.recent.degree_m),
                       sum(df$support.recent.degree_s)
                       )}



        #This runs a plyr package function (ddply) to create ego level variables
        #It takes variables from the all alter level constructed data set and calculates them for each unique ego using the "n" variable
                comp.measures <- ddply(.data= all.alter.constructed.variables,
                                         .variables= .(n), .fun= comp.fun)


        #This calculates the size of each ego network by finding the max alter number for each unique ego (by using "n")
                size.network <- aggregate(x= all.alter.constructed.variables$Alter.Number,
                                            by= list(all.alter.constructed.variables$n),
                                            FUN= function(x) which.max(x))


        #This binds together the data set with all of the ego level constructed variables and the network size variable (max # of alters)
                comp.measures.size <- cbind (comp.measures,size.network[,2])


        #This adds names to the variables in the data set.The names start with the unique ego id variable "n" followed by all of the constructed ego level variable
        #names, then the variable "alters" which is the max # of alters or the networ size


                names(comp.measures.size) <- c("n", "sex_male", "sex_female", "sex_other",
                                                      "homeless.ever", "homeless.in.past", "homeless.recent",
                                                      "interact.rarely", "interact.sometimes", "interact.often",
                                                      "contact.days.tot",
                                                      "sex.with", "sex.with.recent", "sex.with.upsex", "any.upsex",
                                                      "drink.with", "drink.with.recent",
                                                      "drugs.with", "drugs.with.recent",
                                                      "aod.more.with", "aod.more.with.recent",
                                                      "any.risk", "any.risk.recent",
                                                      "em.support", "em.support.recent",
                                                      "info.support", "info.support.recent",
                                                      "tan.support", "tan.support.recent",
                                                      "any.support", "any.support.recent",
                                                      "negative.rel",
                                                      "time.spend.more", "time.spend.more.or.same", "time.spend.less", "time.spend.same",
                                                      "sum.risk.tot", "sum.support.tot", "sum.risk.recent.tot", "sum.support.recent.tot",
                                                      "risk.and.support.tot", "risk.not.support.tot", "support.not.risk.tot",
                                                      "risk.and.support.recent.tot", "risk.not.support.recent.tot", "support.not.risk.recent.tot",
                                                      "risk.degree_w.tot" , "risk.degree_m.tot" , "risk.degree_s.tot" ,
                                                      "risk.recent.degree_w.tot" , "risk.recent.degree_m.tot" , "risk.recent.degree_s.tot" ,
                                                      "support.degree_w.tot" , "support.degree_m.tot" , "support.degree_s.tot" ,
                                                      "support.recent.degree_w.tot" , "support.recent.degree_m.tot" , "support.recent.degree_s.tot" ,
                                                      "alters")

        #This calculates the proportion and avaerage variables based on the constructed variables and the network size


                  comp.measures.size$prop.sex_male                <- round(comp.measures.size$sex_male                             / comp.measures.size$alters, 2)
                  comp.measures.size$prop.sex_female              <- round(comp.measures.size$sex_female                           / comp.measures.size$alters, 2)
                  comp.measures.size$prop.sex_other               <- round(comp.measures.size$sex_other                            / comp.measures.size$alters, 2)
                  comp.measures.size$prop.homeless.ever           <- round(comp.measures.size$homeless.ever                        / comp.measures.size$alters, 2)
                  comp.measures.size$prop.homeless.in.past        <- round(comp.measures.size$homeless.in.past                     / comp.measures.size$alters, 2)
                  comp.measures.size$prop.homeless.recent         <- round(comp.measures.size$homeless.recent                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.interact.rarely         <- round(comp.measures.size$interact.rarely                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.interact.sometimes      <- round(comp.measures.size$interact.sometimes                   / comp.measures.size$alters, 2)
                  comp.measures.size$prop.interact.often          <- round(comp.measures.size$interact.often                       / comp.measures.size$alters, 2)
                  comp.measures.size$prop.ave.contact.days        <- round(comp.measures.size$contact.days.tot                     / comp.measures.size$alters, 2)
                  comp.measures.size$prop.sex.with                <- round(comp.measures.size$sex.with                             / comp.measures.size$alters, 2)
                  comp.measures.size$prop.sex.with.recent         <- round(comp.measures.size$sex.with.recent                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.sex.with.upsex          <- round(comp.measures.size$sex.with.upsex                       / comp.measures.size$alters, 2)
                  comp.measures.size$prop.any.upsex               <- round(comp.measures.size$any.upsex                            / comp.measures.size$alters, 2)
                  comp.measures.size$prop.drink.with              <- round(comp.measures.size$drink.with                           / comp.measures.size$alters, 2)
                  comp.measures.size$prop.drink.with.recent       <- round(comp.measures.size$drink.with.recent                    / comp.measures.size$alters, 2)
                  comp.measures.size$prop.drugs.with              <- round(comp.measures.size$drugs.with                           / comp.measures.size$alters, 2)
                  comp.measures.size$prop.drugs.with.recent       <- round(comp.measures.size$drugs.with.recent                    / comp.measures.size$alters, 2)
                  comp.measures.size$prop.aod.more.with           <- round(comp.measures.size$aod.more.with                        / comp.measures.size$alters, 2)
                  comp.measures.size$prop.aod.more.with.recent    <- round(comp.measures.size$aod.more.with.recent                 / comp.measures.size$alters, 2)
                  comp.measures.size$prop.any.risk                <- round(comp.measures.size$any.risk                             / comp.measures.size$alters, 2)
                  comp.measures.size$prop.any.risk.recent         <- round(comp.measures.size$any.risk.recent                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.em.support              <- round(comp.measures.size$em.support                           / comp.measures.size$alters, 2)
                  comp.measures.size$prop.em.support.recent       <- round(comp.measures.size$em.support.recent                    / comp.measures.size$alters, 2)
                  comp.measures.size$prop.info.support            <- round(comp.measures.size$info.support                         / comp.measures.size$alters, 2)
                  comp.measures.size$prop.info.support.recent     <- round(comp.measures.size$info.support.recent                  / comp.measures.size$alters, 2)
                  comp.measures.size$prop.tan.support             <- round(comp.measures.size$tan.support                          / comp.measures.size$alters, 2)
                  comp.measures.size$prop.tan.support.recent      <- round(comp.measures.size$tan.support.recent                   / comp.measures.size$alters, 2)
                  comp.measures.size$prop.any.support             <- round(comp.measures.size$any.support                          / comp.measures.size$alters, 2)
                  comp.measures.size$prop.any.support.recent      <- round(comp.measures.size$any.support.recent                   / comp.measures.size$alters, 2)
                  comp.measures.size$prop.negative.rel            <- round(comp.measures.size$negative.rel                         / comp.measures.size$alters, 2)
                  comp.measures.size$prop.time.spend.more         <- round(comp.measures.size$time.spend.more                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.time.spend.more.or.same <- round(comp.measures.size$time.spend.more.or.same              / comp.measures.size$alters, 2)
                  comp.measures.size$prop.time.spend.less         <- round(comp.measures.size$time.spend.less                      / comp.measures.size$alters, 2)
                  comp.measures.size$prop.time.spend.same         <- round(comp.measures.size$time.spend.same                      / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk                     <- round(comp.measures.size$sum.risk.tot                         / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.recent              <- round(comp.measures.size$sum.risk.recent.tot                  / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support                  <- round(comp.measures.size$sum.support.tot                      / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.recent           <- round(comp.measures.size$sum.support.recent.tot               / comp.measures.size$alters, 2)
                  comp.measures.size$prop.risk.and.support         <- round(comp.measures.size$risk.and.support.tot                / comp.measures.size$alters, 2)
                  comp.measures.size$prop.risk.not.support         <- round(comp.measures.size$risk.not.support.tot                / comp.measures.size$alters, 2)
                  comp.measures.size$prop.support.not.risk         <- round(comp.measures.size$support.not.risk.tot                / comp.measures.size$alters, 2)
                  comp.measures.size$prop.risk.and.support.recent  <- round(comp.measures.size$risk.and.support.recent.tot         / comp.measures.size$alters, 2)
                  comp.measures.size$prop.risk.not.support.recent  <- round(comp.measures.size$risk.not.support.recent.tot         / comp.measures.size$alters, 2)
                  comp.measures.size$prop.support.not.risk.recent  <- round(comp.measures.size$support.not.risk.recent.tot         / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.degree_w            <- round(comp.measures.size$risk.degree_w.tot                    / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.degree_m            <- round(comp.measures.size$risk.degree_m.tot                    / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.degree_s            <- round(comp.measures.size$risk.degree_s.tot                    / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.recent.degree_w     <- round(comp.measures.size$risk.recent.degree_w.tot             / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.recent.degree_m     <- round(comp.measures.size$risk.recent.degree_m.tot             / comp.measures.size$alters, 2)
                  comp.measures.size$ave.risk.recent.degree_s     <- round(comp.measures.size$risk.recent.degree_s.tot             / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.degree_w         <- round(comp.measures.size$support.degree_w.tot                 / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.degree_m         <- round(comp.measures.size$support.degree_m.tot                 / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.degree_s         <- round(comp.measures.size$support.degree_s.tot                 / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.recent.degree_w  <- round(comp.measures.size$support.recent.degree_w.tot          / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.recent.degree_m  <- round(comp.measures.size$support.recent.degree_m.tot          / comp.measures.size$alters, 2)
                  comp.measures.size$ave.support.recent.degree_s  <- round(comp.measures.size$support.recent.degree_s.tot          / comp.measures.size$alters, 2)



################################
###END OF CUSTOMIZATION AREA #5#
################################


        #This adds other id variables to the ego level constructed variable data set.  The id variable are pulled from the "netdescriptives" data set from the network structure code above


                id.names <- matrix(nrow=length.unique.respondent.ids, ncol=2, byrow=F)

                id.names[,1] <- netdescriptives[,1]
                id.names[,2] <- netdescriptives[,2]


                names(id.names) <- c("Interview_ID", "EgoID")


                comp.measures.size.name <- cbind(id.names, comp.measures.size)

                colnames(comp.measures.size.name)[1] <- c("Interview_ID")
                colnames(comp.measures.size.name)[2] <- c("EgoID")

        #This exports the data set with constructed variables at the ego level

                write.csv( comp.measures.size.name, file=paste('Outputs/compmeasures',wave.name[w],'.csv',sep=""), row.names= F)
}


################################################################
#EGO LEVEL COMPOSITION VARIABLE CONSRUCTION SECTION            #
#THIS SECTION PROCESSES SOME EGOWEB VARIABLES FOR EGO QUESTIONS#
################################################################

#this initializes an R list object that the composition data will be inserted for multiple waves
        comp.list <- vector("list",0)

#this loops through each of the waves of comp data and inserts it into the list created above
        for(w in 1:wave.totals) {

             y              <- read.csv(surv.comp[w], header=TRUE, sep=",")
            comp.list[[w]] <- y

        }

#this gives names to the list objects
        names(comp.list ) <- wave.name


#This reduces the ego-alter data from EgoWeb to ego only
#It produces a data object with one observation per respondent and only ego questions
        ego_data.list <- lapply(comp.list,function(x) {

          an <- match("Alter.Number",names(x))
          an_1 <- an - 1
          n_col <- ncol(x)
          y <- x[!duplicated(x$EgoID),1:an_1]
          return(y)

        })

#This is a check if the number of rows is the expected amount
        lapply(ego_data.list, nrow)



####################################################################################
#CUSTOMIZATION AREA #6:  THE CODE BELOW PROCESSES RAW EGO LEVEL VARIABLES          #
#                        FROM EGOWEB. THE FIRST SECTION IS FOR PROCESSING          #
#                        VARIABLES THAT ARE REPEATED ACROSS SEVERAL WAVES OF       #
#                        INTERVIEWS WITH THE SAME EGOS. THE MULI-WAVE SECTION      #
#                        IS FOLLOWED BY SIMILAR CODE THAT PROCESSES VARIABLES THAT #
#                        ARE NOT CONSISTENT ACROSS WAVES AND ONLY APPEAR IN ONE    #
#                        WAVE (SUCH AS DEMOGRAPHIC QUESTIONS THAT ARE NOT RE-ASKED #
#                        OF THE SAME RESPONDENT/SUBJECT/PARTICIPANT.               #
####################################################################################



##ALL WAVE VARIABLE PROCESSING

##The code below runs the same process for each object (wave) in the list created above
##Any variable that appears across waves should be processed here


        #This calculates the interview time after converting interview time to correct format

        #NOTE: this is in 12 hour format and will produce negative numbers if the interview started
        #in the AM and ended in the PM.  Changing to 24 hour time is on the to do list
        #but this will require changing the code below

                Interview.Times <- lapply(ego_data.list,"[",c("Start.Time", "End.Time"))
                Interview.length <- lapply(Interview.Times, function(x) {

                Start.Time.c       <- as.POSIXct(x$Start.Time)
                End.Time.c         <- as.POSIXct(x$End.Time)
                Int.Time           <- End.Time.c - Start.Time.c
                return(cbind(Int.Time))

        })


        #This double checks to see if the interview length variable looks correct
        #Sometimes there are weird changes in dates (that I can't figure out unfortunately).
        #So this is used to print out interview length to make sure it looks right and not filled with "NA"
        Interview.length

        #Ths adds in the newly created interview time back into the ego level list
        ego_data.list <- mapply(cbind,ego_data.list, Interview.length, SIMPLIFY = "FALSE")

        #This is a check to see if the new variable was added to all waves
        lapply(ego_data.list, names)


        #This calculates the difference between the interview end time and another date variable
        #after converting the egoweb date output and end time to correct format
        HIV.Times <- lapply(ego_data.list,"[",c("HIVTEST1_a", "End.Time"))
        HIV.test.length <- lapply(HIV.Times, function(x) {

          End.Time.c        <- as.POSIXct(x$End.Time)
          HIV.test.c        <- as.POSIXct(x$HIVTEST1_a,format = "%B %d %Y")
          HIV.test.days     <- End.Time.c  - HIV.test.c
          HIV.test.years    <- HIV.test.days / 365
          return(cbind(HIV.test.days, HIV.test.years))

        })

        #this displays the calculation above to make sure it is correct
        HIV.test.length

        #This adds the variable calculated above(time since last HIV test for this example) to the ego level list data
        ego_data.list <- mapply(cbind,ego_data.list, HIV.test.length, SIMPLIFY = "FALSE")

        #This checks if the new variable was added
        lapply(ego_data.list, names)


        #The code steps below take the raw values of a set of scale questions and turns them into
        #an overall score.  Some itesms are reverse coded so they are adjusted

        #READINESS TO CHANGE SUBSTANCE USE (RTCSU)

        #This identifies the RTCSU items and puts them into a list
        RTCSU.vars <- lapply(ego_data.list,"[",c("RTCSU.1", "RTCSU.2", "RTCSU.3", "RTCSU.4", "RTCSU.5", "RTCSU.6", "RTCSU.7", "RTCSU.8", "RTCSU.9", "RTCSU.10", "RTCSU.11", "RTCSU.12"))

        #This is a function that will process the new variables based on the raw variables and calculate an average
        #The steps will be repeated for each wave of data
        RTCSU.ave  <- lapply(RTCSU.vars, function(x) {

        RTCSU.1.rc  <- NA
        RTCSU.2.rc  <- NA
        RTCSU.3.rc  <- NA
        RTCSU.4.rc  <- NA
        RTCSU.5.rc  <- NA
        RTCSU.6.rc  <- NA
        RTCSU.7.rc  <- NA
        RTCSU.8.rc  <- NA
        RTCSU.9.rc  <- NA
        RTCSU.10.rc  <- NA
        RTCSU.11.rc  <- NA
        RTCSU.12.rc  <- NA

        RTCSU.1.rc[x$RTCSU.1==1]   <- 1
        RTCSU.2.rc[x$RTCSU.2==1]   <- 1
        RTCSU.3.rc[x$RTCSU.3==1]   <- 1
        RTCSU.4.rc[x$RTCSU.4==1]   <- 1
        RTCSU.5.rc[x$RTCSU.5==1]   <- -1 #reverse coded
        RTCSU.6.rc[x$RTCSU.6==1]   <- 1
        RTCSU.7.rc[x$RTCSU.7==1]   <- 1
        RTCSU.8.rc[x$RTCSU.8==1]   <- 1
        RTCSU.9.rc[x$RTCSU.9==1]   <- 1
        RTCSU.10.rc[x$RTCSU.10==1]  <- 1
        RTCSU.11.rc[x$RTCSU.11==1]  <- 1
        RTCSU.12.rc[x$RTCSU.12==1]  <- 1


        RTCSU.1.rc[x$RTCSU.1==0]   <- -1
        RTCSU.2.rc[x$RTCSU.2==0]   <- -1
        RTCSU.3.rc[x$RTCSU.3==0]   <- -1
        RTCSU.4.rc[x$RTCSU.4==0]   <- -1
        RTCSU.5.rc[x$RTCSU.5==0]   <- 1 #reverse coded -- positive instead of negative
        RTCSU.6.rc[x$RTCSU.6==0]   <- -1
        RTCSU.7.rc[x$RTCSU.7==0]   <- -1
        RTCSU.8.rc[x$RTCSU.8==0]   <- -1
        RTCSU.9.rc[x$RTCSU.9==0]   <- -1
        RTCSU.10.rc[x$RTCSU.10==0]  <- -1
        RTCSU.11.rc[x$RTCSU.11==0]  <- -1
        RTCSU.12.rc[x$RTCSU.12==0]  <- -1

        RTCSU.1.rc[x$RTCSU.1==-2]   <- 0
        RTCSU.2.rc[x$RTCSU.2==-2]   <- 0
        RTCSU.3.rc[x$RTCSU.3==-2]   <- 0
        RTCSU.4.rc[x$RTCSU.4==-2]   <- 0
        RTCSU.5.rc[x$RTCSU.5==-2]   <- 0
        RTCSU.6.rc[x$RTCSU.6==-2]   <- 0
        RTCSU.7.rc[x$RTCSU.7==-2]   <- 0
        RTCSU.8.rc[x$RTCSU.8==-2]   <- 0
        RTCSU.9.rc[x$RTCSU.9==-2]   <- 0
        RTCSU.10.rc[x$RTCSU.10==-2]  <- 0
        RTCSU.11.rc[x$RTCSU.11==-2]  <- 0
        RTCSU.12.rc[x$RTCSU.12==-2]  <- 0


        #this the main scale that is a sum of all of the values created above
        RTCSU.sum.all <- RTCSU.1.rc +  RTCSU.2.rc +  RTCSU.3.rc +
          RTCSU.4.rc +  RTCSU.5.rc +  RTCSU.6.rc +
          RTCSU.7.rc +  RTCSU.8.rc +  RTCSU.9.rc +
          RTCSU.10.rc +  RTCSU.11.rc +  RTCSU.12.rc

        #these are subscales
          RTCSU.sum.pc   <- RTCSU.1.rc + RTCSU.5.rc + RTCSU.10.rc + RTCSU.12.rc
          RTCSU.sum.c    <- RTCSU.3.rc + RTCSU.4.rc + RTCSU.8.rc + RTCSU.9.rc
          RTCSU.sum.a    <- RTCSU.2.rc + RTCSU.6.rc + RTCSU.7.rc + RTCSU.11.rc

        #this returns all of the variables calculated above
          return(cbind(RTCSU.sum.all, RTCSU.sum.pc, RTCSU.sum.c, RTCSU.sum.a))
        })

        #This displays if the values were calculated OK
        #RTCSU.ave


        #This adds the variables calculated above for the RTCSU scale to the ego list data
        ego_data.list <- mapply(cbind,ego_data.list, RTCSU.ave, SIMPLIFY = "FALSE")

        #This checks to make sure that the new variable was added to each wave
        lapply(ego_data.list, names)




        #READINESS TO CHANGE RISKY SEX (RTCRS)

        #This identifies the RTCRS items and puts them into a list
        RTCRS.vars <- lapply(ego_data.list,"[",c("RTCRS.1", "RTCRS.2", "RTCRS.3", "RTCRS.4", "RTCRS.5", "RTCRS.6", "RTCRS.7", "RTCRS.8", "RTCRS.9", "RTCRS.10", "RTCRS.11"))

        #This is a function that will process the new variables based on the raw variables and calculate an average
        #The steps will be repeated for each wave of data
        RTCRS.ave  <- lapply(RTCRS.vars, function(x) {

        RTCRS.1.rc  <- NA
        RTCRS.2.rc  <- NA
        RTCRS.3.rc  <- NA
        RTCRS.4.rc  <- NA
        RTCRS.5.rc  <- NA
        RTCRS.6.rc  <- NA
        RTCRS.7.rc  <- NA
        RTCRS.8.rc  <- NA
        RTCRS.9.rc  <- NA
        RTCRS.10.rc  <- NA
        RTCRS.11.rc  <- NA
        RTCRS.12.rc  <- NA

        RTCRS.1.rc[x$RTCRS.1==1]   <- 1
        RTCRS.2.rc[x$RTCRS.2==1]   <- 1
        RTCRS.3.rc[x$RTCRS.3==1]   <- 1
        RTCRS.4.rc[x$RTCRS.4==1]   <- 1
        RTCRS.5.rc[x$RTCRS.5==1]   <- -1 #reverse coded
        RTCRS.6.rc[x$RTCRS.6==1]   <- 1
        RTCRS.7.rc[x$RTCRS.7==1]   <- 1
        RTCRS.8.rc[x$RTCRS.8==1]   <- 1
        RTCRS.9.rc[x$RTCRS.9==1]   <- 1
        RTCRS.10.rc[x$RTCRS.10==1]  <- 1
        RTCRS.11.rc[x$RTCRS.11==1]  <- 1


        RTCRS.1.rc[x$RTCRS.1==0]   <- -1
        RTCRS.2.rc[x$RTCRS.2==0]   <- -1
        RTCRS.3.rc[x$RTCRS.3==0]   <- -1
        RTCRS.4.rc[x$RTCRS.4==0]   <- -1
        RTCRS.5.rc[x$RTCRS.5==0]   <- 1 #reverse coded -- positive instead of negative
        RTCRS.6.rc[x$RTCRS.6==0]   <- -1
        RTCRS.7.rc[x$RTCRS.7==0]   <- -1
        RTCRS.8.rc[x$RTCRS.8==0]   <- -1
        RTCRS.9.rc[x$RTCRS.9==0]   <- -1
        RTCRS.10.rc[x$RTCRS.10==0]  <- -1
        RTCRS.11.rc[x$RTCRS.11==0]  <- -1

        RTCRS.1.rc[x$RTCRS.1==-2]   <- 0
        RTCRS.2.rc[x$RTCRS.2==-2]   <- 0
        RTCRS.3.rc[x$RTCRS.3==-2]   <- 0
        RTCRS.4.rc[x$RTCRS.4==-2]   <- 0
        RTCRS.5.rc[x$RTCRS.5==-2]   <- 0
        RTCRS.6.rc[x$RTCRS.6==-2]   <- 0
        RTCRS.7.rc[x$RTCRS.7==-2]   <- 0
        RTCRS.8.rc[x$RTCRS.8==-2]   <- 0
        RTCRS.9.rc[x$RTCRS.9==-2]   <- 0
        RTCRS.10.rc[x$RTCRS.10==-2]  <- 0
        RTCRS.11.rc[x$RTCRS.11==-2]  <- 0


        #this the main scale that is a sum of all of the values created above
        RTCRS.sum.all <- RTCRS.1.rc +  RTCRS.2.rc +  RTCRS.3.rc +
          RTCRS.4.rc +  RTCRS.5.rc +  RTCRS.6.rc +
          RTCRS.7.rc +  RTCRS.8.rc +  RTCRS.9.rc +
          RTCRS.10.rc +  RTCRS.11.rc

        #these are subscales
          RTCRS.sum.pc   <- RTCRS.1.rc + RTCRS.5.rc + RTCRS.10.rc
          RTCRS.sum.c    <- RTCRS.3.rc + RTCRS.4.rc + RTCRS.8.rc + RTCRS.9.rc
          RTCRS.sum.a    <- RTCRS.2.rc + RTCRS.6.rc + RTCRS.7.rc + RTCRS.11.rc

        #this returns all of the variables calculated above
          return(cbind(RTCRS.sum.all, RTCRS.sum.pc, RTCRS.sum.c, RTCRS.sum.a))
        })

        #This displays if the values were calculated OK
        #RTCRS.ave


        #This adds the variables calculated above for the RTCRS scale to the ego list data
        ego_data.list <- mapply(cbind,ego_data.list, RTCRS.ave, SIMPLIFY = "FALSE")

        #This checks to make sure that the new variable was added to each wave
        lapply(ego_data.list, names)



        #The code steps below calculate quantity frequency variables by taking scale
        #responses and assigning them # of days and multiplying that by an average for each day
        #The same steps are repated for each type of substance

        # DAYS DRINKING, TOTAL DRINKS, BINGE DRINKING

        #This identifies the appropriate variables
        drinking.vars <- lapply(ego_data.list,"[",c("ALC3", "ALC4", "ALC5"))

        #this is the function that assigns #s of days based on the response options for the questions
        drinking.tots  <- lapply(drinking.vars, function(x) {

        #this calculates days drinking

        daysALC3              <- NA
        daysALC3[x$ALC3<=0]  <- 0
        daysALC3[x$ALC3==1]   <- 1
        daysALC3[x$ALC3==2]   <- 2
        daysALC3[x$ALC3==3]   <- 3
        daysALC3[x$ALC3==4]   <- 4
        daysALC3[x$ALC3==5]   <- 8
        daysALC3[x$ALC3==6]   <- 16
        daysALC3[x$ALC3==7]   <- 24
        daysALC3[x$ALC3==8]   <- 28


        #this calculates total drinks

        total_drinks_4weeks                     <- daysALC3 * x$ALC4
        total_drinks_4weeks[x$ALC3<=0]          <- 0
        total_drinks_4weeks[total_drinks_4weeks>136]          <- 136

        #this calculates binge drinking

        daysbinge                          <-  NA
        daysbinge[x$ALC3<=0]     <-  0
        daysbinge[x$ALC5==0]     <-  0
        daysbinge[x$ALC5==1]     <-  1
        daysbinge[x$ALC5==2]     <-  2
        daysbinge[x$ALC5==3]     <-  3
        daysbinge[x$ALC5==4]     <-  4
        daysbinge[x$ALC5==5]     <-  8
        daysbinge[x$ALC5==6]     <-  16
        daysbinge[x$ALC5==7]     <-  24
        daysbinge[x$ALC5==8]     <-  28

        #this sets up the outcome variables calculated above for export
        return(cbind(daysALC3, total_drinks_4weeks , daysbinge))
        })

        #This checks if the variables calculated above look OK
        drinking.tots


        #this adds the vairalbe calculated above to the ego list data
        ego_data.list <- mapply(cbind,ego_data.list, drinking.tots, SIMPLIFY = "FALSE")

        #this checks if the new variables were added correctly to each wave
        lapply(ego_data.list, names)


        #This creates some variables from raw responses about sex






        #This identifies the appropriate variables
        sex.vars <- lapply(ego_data.list,"[",c("SEX1", "SEX2", "SEX2a", "SEX3", "SEX4", "SEX5"))

        #this is the function that assigns #s of days based on the response options for the questions
        sex.tots  <- lapply(sex.vars, function(x) {

        #this creates sex partner variable

        sex.part.tots                   <-  x$SEX1
        sex.part.tots[sex.part.tots<0]  <-  0

        sex.times              <- x$SEX2
        sex.times[sex.times<0] <- 0

        condom.times                    <- x$SEX2a
        condom.times[condom.times<0]    <- 0

        unprotected.sex.times      <- sex.times - condom.times

        sex.times_trunc         <- sex.times
        sex.times_trunc[sex.times>55.39] <- 55.39

        unprotected.sex.times_trunc         <- sex.times_trunc - condom.times


        concurrent.sex                          <- x$SEX3
        concurrent.sex[concurrent.sex<0]        <- 0

        sex.exchange.give                       <- x$SEX4
        sex.exchange.rec                        <- x$SEX5
        sex.exchange.give[sex.exchange.give<0]  <- 0
        sex.exchange.rec[sex.exchange.rec<0]    <- 0



        #this sets up the outcome variables calculated above for export
        return(cbind(sex.part.tots, sex.times, sex.times_trunc, condom.times, unprotected.sex.times, unprotected.sex.times_trunc,concurrent.sex, sex.exchange.rec, sex.exchange.give))
        })

        #This checks if the variables calculated above look OK
        sex.tots


        #this adds the vairalbe calculated above to the ego list data
        ego_data.list <- mapply(cbind,ego_data.list, sex.tots, SIMPLIFY = "FALSE")

        #this checks if the new variables were added correctly to each wave
        lapply(ego_data.list, names)




        # OTHER DRUG USE
        # This calculates quantity of other drug use

        #This identifies the variables to be processed in the function below
        drug.vars <- lapply(ego_data.list,"[",c("DRUG1a", "DRUG2a", "DRUG3a", "DRUG4a", "DRUG5a", "DRUG6a", "DRUG7a"))

        #This is the function that will assign each variable response option to a number of days
        drug.tots  <- lapply(drug.vars, function(x) {


        daysmj                  <-  NA
        daysmj[x$DRUG1a<=0]     <-  0
        daysmj[x$DRUG1a==1]     <-  1
        daysmj[x$DRUG1a==2]     <-  2
        daysmj[x$DRUG1a==3]     <-  3
        daysmj[x$DRUG1a==4]     <-  4
        daysmj[x$DRUG1a==5]     <-  8
        daysmj[x$DRUG1a==6]     <-  16
        daysmj[x$DRUG1a==7]     <-  24
        daysmj[x$DRUG1a==8]     <-  28

        dayscoke                  <-  NA
        dayscoke[x$DRUG2a<=0]     <-  0
        dayscoke[x$DRUG2a==1]     <-  1
        dayscoke[x$DRUG2a==2]     <-  2
        dayscoke[x$DRUG2a==3]     <-  3
        dayscoke[x$DRUG2a==4]     <-  4
        dayscoke[x$DRUG2a==5]     <-  8
        dayscoke[x$DRUG2a==6]     <-  16
        dayscoke[x$DRUG2a==7]     <-  24
        dayscoke[x$DRUG2a==8]     <-  28


        dayscrack                  <-  NA
        dayscrack[x$DRUG3a<=0]     <-  0
        dayscrack[x$DRUG3a==1]     <-  1
        dayscrack[x$DRUG3a==2]     <-  2
        dayscrack[x$DRUG3a==3]     <-  3
        dayscrack[x$DRUG3a==4]     <-  4
        dayscrack[x$DRUG3a==5]     <-  8
        dayscrack[x$DRUG3a==6]     <-  16
        dayscrack[x$DRUG3a==7]     <-  24
        dayscrack[x$DRUG3a==8]     <-  28


        daysheroin                  <-  NA
        daysheroin[x$DRUG4a<=0]     <-  0
        daysheroin[x$DRUG4a==1]     <-  1
        daysheroin[x$DRUG4a==2]     <-  2
        daysheroin[x$DRUG4a==3]     <-  3
        daysheroin[x$DRUG4a==4]     <-  4
        daysheroin[x$DRUG4a==5]     <-  8
        daysheroin[x$DRUG4a==6]     <-  16
        daysheroin[x$DRUG4a==7]     <-  24
        daysheroin[x$DRUG4a==8]     <-  28



        dayspain                  <-  NA
        dayspain[x$DRUG5a<=0]     <-  0
        dayspain[x$DRUG5a==1]     <-  1
        dayspain[x$DRUG5a==2]     <-  2
        dayspain[x$DRUG5a==3]     <-  3
        dayspain[x$DRUG5a==4]     <-  4
        dayspain[x$DRUG5a==5]     <-  8
        dayspain[x$DRUG5a==6]     <-  16
        dayspain[x$DRUG5a==7]     <-  24
        dayspain[x$DRUG5a==8]     <-  28


        daysspeed                  <-  NA
        daysspeed[x$DRUG6a<=0]     <-  0
        daysspeed[x$DRUG6a==1]     <-  1
        daysspeed[x$DRUG6a==2]     <-  2
        daysspeed[x$DRUG6a==3]     <-  3
        daysspeed[x$DRUG6a==4]     <-  4
        daysspeed[x$DRUG6a==5]     <-  8
        daysspeed[x$DRUG6a==6]     <-  16
        daysspeed[x$DRUG6a==7]     <-  24
        daysspeed[x$DRUG6a==8]     <-  28



        daysother                  <-  NA
        daysother[x$DRUG7a<=0]     <-  0
        daysother[x$DRUG7a==1]     <-  1
        daysother[x$DRUG7a==2]     <-  2
        daysother[x$DRUG7a==3]     <-  3
        daysother[x$DRUG7a==4]     <-  4
        daysother[x$DRUG7a==5]     <-  8
        daysother[x$DRUG7a==6]     <-  16
        daysother[x$DRUG7a==7]     <-  24
        daysother[x$DRUG7a==8]     <-  28


        #This sets up the variables calculated above for export
        return(cbind(daysmj, dayscoke, dayscrack, dayspain, daysspeed, daysother))

        })
        #This checks if the variables look OK
        #drug.tots


        #Ths adds the variables calculated above to the ego list data
        ego_data.list <- mapply(cbind,ego_data.list, drug.tots, SIMPLIFY = "FALSE")

        #check if the new variables were added
        lapply(ego_data.list, names)



        #The code below converts multiple response variables that allow for more than one response
        #into separate responses.  This necessary because egoweb exports these variables as a string
        #of responses separated by semi-colons (e.g. "1;3;7"). The code below turns the variable
        #into several variables with differen numeric suffixes

        #this library is necessary for the function below
        library(splitstackshape)

        #This requires a for loop.  (I tried to do this in a funciton but was unable)
        for(w in 1:wave.totals) {

        #These are the two variables in this example data set that allow more than one response
        #and will be converted
          ego_data.list[[w]]  <- cSplit(ego_data.list[[w]], "RESID6", sep=";", type.convert=TRUE)
          ego_data.list[[w]]  <- cSplit(ego_data.list[[w]], "RTCSU.PRE", sep=";", type.convert=TRUE)

        }

        #This displays the variable names to make sure they look correct
        lapply(ego_data.list, names)





##SINGLE WAVE VARIABLE PROCESSING
##THE CODE BELOW IS FOR VARIABLES THAT ONLY APPEAR IN ONE WAVE
##This code is necessary because the functions above would not work when there was
##a varialbe missing from a wave.  There is probably a way to tweak them but this was the
##only way I could get them to run. They do not require the list functionality


        #This splits out multiple selection variable that only occurs in baseline
        ego_data.list[[1]] <- cSplit(ego_data.list[[1]], "RESID1", sep=";", type.convert=TRUE)

        #This checks to see if they appear correctly
        lapply(ego_data.list, names)


        #This calculates the baseline only date variables
        End.Time.c       <- as.POSIXct(ego_data.list[[1]]$End.Time)
        DOB.c            <- as.POSIXct(ego_data.list[[1]]$DEMO2,format = "%B %d %Y")
        age.days          <- End.Time.c - DOB.c
        age.years         <- age.days / 365

        #This adds the new variables created above to the baseline data
        ego_data.list[[1]] <- cbind(ego_data.list[[1]], age.days)
        ego_data.list[[1]] <- cbind(ego_data.list[[1]], age.years)

        #This checks to see if they appear correctly
        lapply(ego_data.list, names)


################################
###END OF CUSTOMIZATION AREA #6#
################################



#This saves the data to an r object
save(ego_data.list, file="ego_data.list.rda")


#This outputs a separate .csv file for each wave and names it based on the list of wave names
        for(w in 1:wave.totals) {

          write.csv(ego_data.list[[w]], file=(paste('Outputs/ego.raw.constructed.vars',wave.name[w],'.csv',sep="")), row.names= T)

        }










