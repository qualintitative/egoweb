
<!-- listOfAlters.js                                   -->
<!-- javaScript functions specific to keyboard control -->
<!-- of the list-of-alters page                        -->
<!-- KCN May 29 2010                                   -->

// jQuery version 1.4.2 should be loaded before this script.

jQuery.noConflict();

var loaRowList = null;
var loaColList = null;
var loaCurrentRow = null;
var iloaCurrentRow = null;
var iloaCurrentCol = null;
var loaInitialized = false;
var loaCheckBoxes = null;
var loaLabels = null;

//============================================================
// loaInitialize
// needs to be called once per page to initialize the arrays
// used.  horizontal is a boolean indicating the orientation
// of the multiple selection questions.
//============================================================

function loaInitialize(horizontal) {

    var iy = 0;

    if ( !loaInitialized ) {
        loaRowList = new Array();
        loaCheckBoxes = new Array();
        loaLabels = new Array(); 
        tempList = document.getElementsByTagName("div");

        if ( horizontal ) {
            for ( ix=0 ; ix<tempList.length ; ++ix ) {
                if ( tempList[ix].id != null && tempList[ix].id.match("horizontalForm*")) {
                    loaRowList[iy] = tempList[ix];
                    ++iy;
                }
            }
        } else {
             for ( ix=0 ; ix<tempList.length ; ++ix ) {
                if ( tempList[ix].id != null && tempList[ix].id.match("verticalForm*")) {
                    loaRowList[iy] = tempList[ix];
                    ++iy;
                }
            }
        }

        // ==============================================
        // now get the array of input and associated spans
        // the spans are the labels of the checkboxes.
        // this is needed to set the background of the 
        // labels associated with checkboxes, as firefox does
        // not let you do much with the style of a checkbox
        // now get array of <input>
        tempList = document.getElementsByTagName("input");
        // alert (tempList.length);
        iy = 0;
        for ( ix=0 ; ix<tempList.length ; ++ix ) {
            if ( tempList[ix].className != null  && tempList[ix].className.match("hotkey")) {
                loaCheckBoxes[iy] = tempList[ix];
                ++iy;
             }
        }
        // alert(loaCheckBoxes.length);
        // now get an array of <span> and also limit them
        // to ones with a className of hotkey.
        tempList = document.getElementsByTagName("span");
        // alert (tempList.length);
        iy = 0;
        for ( ix=0 ; ix<tempList.length ; ++ix ) {
            if ( tempList[ix].className != null  && tempList[ix].className.match("hotkey")) {
                loaLabels[iy] = tempList[ix];
                ++iy;
             }
        }
        // alert(loaLabels.length);

        //=======================================
        // lastly, set the initial row and column
        iloaCurrentRow = 0;
        iloaCurrentCol = 0;
        loaCurrentRow = loaRowList[iloaCurrentRow];
        if ( horizontal )
            loaCurrentRow.className = "loaHiliteRow";

        loaColList = loaCurrentRow.getElementsByTagName("input");
        loaColList[iloaCurrentCol].focus();
        loaColList[iloaCurrentCol].className = "loaHiliteItem";
    }
    loaInitialized = true;
}

//===========================================================
//  loaGetLabelForCheckBox
//  if cbox is in the array of checkboxes ( loaCheckBoxes )
//  this will return the corresponding element in the loalabels
//  array, which *should* be the label associated with it
//===========================================================

function loaGetLabelForCheckBox ( cbox ) {

    var retLabel = null;

    if ( loaCheckBoxes==null  ||  loaLabels==null )
        return(retLabel);
    
    for ( ix=0 ; ix<loaCheckBoxes.length ; ++ix ) {
        if ( loaCheckBoxes[ix] == cbox )
            return (loaLabels[ix]);
    }

    return (retLabel);
}

//===========================================================
// loaIsInRowList
//===========================================================

function loaIsInRowList ( object ) {

   for ( ix=0 ; ix<loaRowList.length ; ++ix ) {
       if ( loaRowList[ix] == object )
            return (true);
   }
   return(false);
}

//===========================================================
// loaNextInRowList
//===========================================================

function loaNextInRowList ( object, forward ) {
    
    var iLength = loaRowList.length;

    for ( ix=0 ; ix<iLength ; ++ix ) {
        if ( loaRowList[ix] == object ) {
            if ( forward )
                return ( loaRowList[(ix+1)%iLength] );
            else
                return ( loaRowList[(ix+iLength-1)%iLength]);
        }
    }
   return(null);
}


//===========================================================
// loaFindAncestorOnRowList
//===========================================================

function loaFindAncestorOnRowList ( object ) {

	if ( object == null )
          return(object);

    thisParent = object.parentNode;

      while ( thisParent !=null ) {
           found = loaIsInRowList(thisParent );
           if ( found )
               return (thisParent);
           thisParent = thisParent.parentNode;
      }
      return(null);
}

//========================================================
// loaFindInColList
//========================================================

function loaFindInColList ( object ) {
    
    for ( ix=0 ; ix<loaColList.length ; ++ix )  {
        if (loaColList[ix] == object ) 
            return(ix);
    }
    return(0);
}


//========================================================
// doOnFocusHorz
// object parameter will be a checkbox
//========================================================

function doOnFocusHorz(object) {

      var ix;
      var init;
      var cbLabel;

      init = loaInitialized;
      if ( !loaInitialized ) 
          loaInitialize(true);

      object.className = "loaHiliteItem";

      thisParent = loaFindAncestorOnRowList(object);

      if ( thisParent != loaCurrentRow  ||  !init ) {
          if ( loaCurrentRow != null ) {
              loaCurrentRow.className = "loaNormalRow";
              for ( ix=0 ; ix<loaColList.length ; ++ix )
                  loaColList[ix].className = "loaNormalRow";
		}
	     loaCurrentRow = thisParent ;

         loaCurrentRow.className = "loaHiliteRow";
      }
    loaColList = loaCurrentRow.getElementsByTagName("input");
    iloaCurrentCol = loaFindInColList(object);
    loaColList[iloaCurrentCol].focus();
    loaColList[iloaCurrentCol].className = "loaHiliteItem";

    cbLabel = loaGetLabelForCheckBox(loaColList[iloaCurrentCol]);
    if ( cbLabel!=null )
        cbLabel.className = "loaHiliteItem";
    // addHilite(cbLabel);

}

//========================================================
// doOnFocusVert
// called when a checkbox gains focus and the orientation
// is vertical
//========================================================

function doOnFocusVert(object) {

      var ix;
      var init;
      var cbLabel;

      init = loaInitialized;
      if ( !loaInitialized )
          loaInitialize(false);

      object.className = "loaHiliteItem";

      thisParent = loaFindAncestorOnRowList(object);

      if ( thisParent != loaCurrentRow  ||  !init ) {
          if ( loaCurrentRow != null ) {
              loaCurrentRow.className = "loaNormalRow";
              for ( ix=0 ; ix<loaColList.length ; ++ix )
                  loaColList[ix].className = "loaNormalRow";
		}
	     loaCurrentRow = thisParent ;

         // loaCurrentRow.className = "loaHiliteRow";
      }
    loaColList = loaCurrentRow.getElementsByTagName("input");
    iloaCurrentCol = loaFindInColList(object);
    loaColList[iloaCurrentCol].focus();
    loaColList[iloaCurrentCol].className = "loaHiliteItem";

    cbLabel = loaGetLabelForCheckBox(loaColList[iloaCurrentCol]);
    if ( cbLabel != null )
        cbLabel.className = "loaHiliteItem";
   // addHilite(cbLabel);
}

//===========================================================
// doOnBlur
//===========================================================

function doOnBlur(object) {

    var cbLabel;

    object.className = "loaNormalRow";
    cbLabel = loaGetLabelForCheckBox(object);
    remHilite(cbLabel);
}

//===========================================================
// doOnKeyUpHorz
// when checkboxes are laid out horizontally they are stacked
// on on top of each other, each row is a grouping of checkboxes
// for one alter, the column indicating different checkboxes
// within that row
//===========================================================

function doOnKeyUpHorz(event) {
     var nextCol = iloaCurrentCol;
     var nextDiv = loaCurrentRow;
     var unicode=event.keyCode? event.keyCode : event.charCode;

     if ( unicode==38 ) {
         nextDiv = loaNextInRowList ( loaCurrentRow, false );
     } else if ( unicode==40 ) {
         nextDiv = loaNextInRowList ( loaCurrentRow, true);
     } else if ( unicode==39 ) {
         nextCol = (iloaCurrentCol+1) % loaColList.length;
     } else if ( unicode==37 ) {
         nextCol = iloaCurrentCol-1;
         if ( nextCol<0 ) 
             nextCol = loaColList.length-1;
     }

      if ( nextDiv!=null  &&  nextDiv != loaCurrentRow ) {
          if ( loaCurrentRow != null ) 
              loaCurrentRow.className = "loaNormalRow";
	    loaCurrentRow = nextDiv ;
          loaCurrentRow.className = "loaHiliteRow"; 
  	    loaColList = loaCurrentRow.getElementsByTagName("input");
          if ( iloaCurrentCol >= loaColList.length )
              iloaCurrentCol = loaColList.length-1;
          loaColList[iloaCurrentCol].focus();
	    loaColList[iloaCurrentCol].classname = "loaHiliteItem";
          // event.preventDefault();
          event.returnValue = false;
      } else if ( nextCol!=iloaCurrentCol ) {
          iloaCurrentCol = nextCol;
          loaColList[iloaCurrentCol].focus();
          loaColList[iloaCurrentCol].className = "loaHiliteItem";
	    // event.preventDefault();
          event.returnValue = false;
      } else {
          event.returnValue = true;
      }
}

//===========================================================
// doOnKeyUpVert
// when the list of checkboxes is vertical, we no longer
// have what were once 'rows', and what was previously
// the column is now the 'row' index
//===========================================================

function doOnKeyUpVert(event) {
     var nextCol = iloaCurrentCol;
     var unicode=event.keyCode? event.keyCode : event.charCode;

     if ( unicode==38 || unicode==37 ) {
         nextCol = iloaCurrentCol-1;
         if ( nextCol<0 )
             nextCol = loaColList.length-1;
     } else if ( unicode==40 || unicode==39 ) {
         nextCol = (iloaCurrentCol+1) % loaColList.length;
     }

      if ( nextCol!=iloaCurrentCol ) {
          iloaCurrentCol = nextCol;
          loaColList[iloaCurrentCol].focus();
          loaColList[iloaCurrentCol].className = "loaHiliteItem";
          // event.preventDefault();
          // event.stopPropagation();
          event.returnValue = false;
      } else {
          event.returnValue = true;
      }
}

//========================================================
// addHilite
//========================================================

function addHilite(cbox) {
   if ( cbox != null )
       jQuery(cbox).addClass("loaHiliteItem");
}

//========================================================
// remHilite
//========================================================

function remHilite(cbox) {
    if ( cbox != null )
        jQuery(cbox).removeClass("loaHiliteItem");
}
