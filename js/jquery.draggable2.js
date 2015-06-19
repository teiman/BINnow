
$.fn.draggable = function(options) {


    options = $.extend({
        distance: 0,
        draggingClass: "dragging",
        dragOverClass: "dragOver"
    }, options);

    var offset, margins, startPos, downEvt, helper, passedDistance, hoveredCandidate,
        dropTargets, currentDropTarget, draggable, onMouseEnter, onMouseLeave, dragOverSelector, dragOverClass, tmpOpts = [];

    dragOverSelector = options.dragOverSelector;
    dragOverClass = options.dragOverClass;

    onMouseEnter = function() {
        $(this).addClass( dragOverClass );
        //tei//IScope.KeyWatch.widget.DropManager.notifyDragOver( this, dragOverSelector, draggable );
        hoveredCandidate = this;
    };
    onMouseLeave = function() {
        $(this).removeClass( dragOverClass );
        //tei//IScope.KeyWatch.widget.DropManager.notifyDragOut( this, dragOverSelector, draggable );
        hoveredCandidate = null;
    };

    $(this).live("mousedown.draggable", function(e) {
        if($(this).hasClass(options.draggingClass) || (helper && passedDistance) || e.metaKey || e.shiftKey || e.ctrlKey ||e.button ) return;

        draggable = $(this);

        margins = {
            left: (parseInt(draggable.css("marginLeft"), 10) || 0),
            top: (parseInt(draggable.css("marginTop"), 10) || 0)
        };       

        offset = draggable.offset();
        offset = { top: e.pageY - offset.top + margins.top, left: e.pageX - offset.left + margins.left };
        //offset = { top: e.pageY  + margins.top, left: e.pageX - offset.left + margins.left };

        //offset = { top: e.pageY + margins.top, left: e.pageX + margins.left };
        //draggable.html("data:"+e.pageY);

        //console.log("offset:"+offset.top);
        //console.dir(offset);

        //Helper
        if($.isFunction(options.helper)) {
            helper = options.helper.call(draggable, function(option, value) {
                options[option] = value;
                tmpOpts.push(option);
            });
            if(!helper) throw("DOM node not returned from helper function");
        }
        else {
            helper = draggable.clone();
        }

        helper.addClass(options.draggingClass).css({
            position: "absolute"
        });

        startPos = {
            top: e.pageY - offset.top + "px",
            left: e.pageX - offset.left + "px"
        };

        //console.log("sP:"+startPos);
        //console.dir(startPos);

        $(document).bind("mousemove.draggable", drag).bind("mouseup.draggable", dragup);


        $( dragOverSelector )
            .live('mouseenter', onMouseEnter)
            .live('mouseleave', onMouseLeave);


        downEvt = e;
        e.preventDefault();

        if ( $.isFunction(options.startDrag) ) options.startDrag( e, draggable );
    });

    function drag(e) {

        if(!passedDistance) {
            if(Math.max(Math.abs(downEvt.pageX - e.pageX),
                        Math.abs(downEvt.pageY - e.pageY)) >= options.distance) {
                passedDistance = true;
                if(options.cursorAt) {
                    if(options.cursorAt.top) offset.top = options.cursorAt.top + margins.top;
                    if(options.cursorAt.left) offset.left = options.cursorAt.left + margins.left;
                }

                
                //console.log("Drag:");
                //console.dir(offset);
                //console.dir(downEvt);

                //offset.top =  options.cursorAt.top;

                helper.appendTo("body");

                //labelizar(downEvt,helper);
                //console.dir(helper[0]);
            }
            else
                return;
        }

        //labelizar(e.pageX,helper[0]);//debug, servia para mostrar columnas marcadas para mover

        helper.css({
            top: e.pageY - offset.top + offset.top -16  + "px",
            left: e.pageX - offset.left + "px"
        });

        //console.log("helper:Y:"+ offset.top);

        //check if we are still over the current  target
        /*if(currentDropTarget) {
            var cur = currentDropTarget;
            if(!(e.pageX > cur.x && e.pageX < cur.x + cur.width &&
               e.pageY > cur.y && e.pageY < cur.y + cur.height)) {
                cur.el.removeClass(cur.options.hoverClass);
                currentDropTarget = false;
                return;
            }
        }

        $.each(dropTargets, function(i) {
            if(e.pageX > this.x && e.pageX < this.x + this.width &&
              (e.pageY > this.y && e.pageY < this.y + this.height)) {

               currentDropTarget = this;
               this.el.addClass(currentDropTarget.options.hoverClass);
               return false;
            }
        });*/
    }

    function dragup(e) {
        $(document).unbind("mousemove.draggable", drag).unbind("mouseup.draggable", dragup);

        $( dragOverSelector )
        .die('mouseenter', onMouseEnter)
        .die('mouseleave', onMouseLeave);

//        if(currentDropTarget) {
//            helper.remove();
//            currentDropTarget.el.removeClass(currentDropTarget.options.hoverClass);
//            if($.isFunction(currentDropTarget.options.drop)) {
//                currentDropTarget.options.drop.call(currentDropTarget.el, {
//                    helper: helper,
//                    draggable: draggable,
//                    position: { x: e.pageX, y: e.pageY }
//                });
//            }
//
//            cleanUpVars();
//        }
//        else {

            helper.animate(startPos,1, function() {
                $(this).remove();
                if ( $.isFunction(options.endDrag) ) options.endDrag( e, this );
                cleanUpVars();
            });


            $(hoveredCandidate).removeClass( dragOverClass );
            //tei//IScope.KeyWatch.widget.DropManager.notifyDrop( hoveredCandidate, dragOverSelector, draggable );
            //IScope.log( [this,hoveredCandidate,this==hoveredCandidate] );
            //
            //console.log("Scope-hoveredCandidate:"+hoveredCandidate);
            //console.log("Scope:"+dragOverSelector);
            //console.log("Scope:"+draggable);
            //console.log(draggable);

//        }
    }

    function cleanUpVars() {
        $.each(tmpOpts, function() {
            delete options[this];
        });
        tmpOpts = [];
        offset = margins = startPos = downEvt = helper = passedDistance = dropTargets = hoveredCandidate = currentDropTarget = draggable = null;
    }

    //Prevent text selection in IE
    if ($.browser.msie) {
        $(this).attr('unselectable', 'on');
    }



    return this;
};

$.fn.droppable = function(options) {
    options = $.extend({
        hoverClass: 'draghovered'
    }, options);

    var self = $(this);

    self.data("drop_options", options);
    $.dd.targets.push(this.selector); //must use a selector

    return this;
};


$.dd = {
    targets: []
};

