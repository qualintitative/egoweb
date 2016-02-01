;(function(undefined) {
	'use strict';

	if (typeof sigma === 'undefined') {
		throw 'sigma is not declared';
	}

	sigma.utils.pkg('sigma.events');

	/**
	 * Dispatch 'drag' and 'drop' events by dealing with mouse events.
	 *
	 * @param {object} renderer The renderer to listen.
	 */
	sigma.events.drag = function(renderer) {
		sigma.classes.dispatcher.extend(this);

		var _self = this,
			_drag = false,
			_x = 0,
			_y = 0,
			mX = 0,
			mY = 0,
			node = false;

		// Set _drag to true if the mouse position has changed.
		var detectDrag = function(e) {
			//console.log('mousemove');
			if(Math.abs(e.clientX - _x) || Math.abs(e.clientY - _y) > 1) {
				_drag = true;
				_self.dispatchEvent('drag');
			}
		};

		// Initialize the mouse position and attach the 'mousemove' event
		// to detect dragging.
		renderer.container.addEventListener('mousedown', function(e) {
			_drag = false;
			node = false;
			_x = e.offsetX;
			_y = e.offsetY;
			for(var k in s.graph.nodes()){
				mX = s.graph.nodes()[k]['renderer1:x'];
				mY = s.graph.nodes()[k]['renderer1:y'];
				if(mX >= _x -s.graph.nodes()[k].size &&	mX <= _x + s.graph.nodes()[k].size && mY >= _y - s.graph.nodes()[k].size &&	mY <= _y + s.graph.nodes()[k].size)
					node = s.graph.nodes()[k];
			}
			renderer.container.addEventListener('mousemove', detectDrag);
		});

		renderer.container.addEventListener('mouseup', function(e) {
			// 'mouseup' event is called at the end of the call stack
			// so that 'mousemove' is called before.
			setTimeout(function() {
				if (_drag) {
					_self.dispatchEvent('drop', {
						node:node
					});
				}
				_drag = false;
				renderer.container.removeEventListener('mousemove', detectDrag);
			}, 1);
		});
	};
}).call(this);