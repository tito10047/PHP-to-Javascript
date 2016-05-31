if (typeof N == 'undefined') N = {};
if (typeof N.PhpJs == 'undefined') N.PhpJs = {};
if (typeof N.PhpJs.HashArray == 'undefined') {
	N.PhpJs.HashArray = (function () {
		function HashArray() {
			//TODO: refractor global properties to getters and setters
			this.___keys = [];
			this.___key_pos = 0;
			this.___key_int = 0;
		}

		HashArray.prototype.___keys = [];
		HashArray.prototype.___key_pos = 0;
		HashArray.prototype.set = function (key, value) {
			this.___keys.push(key);
			this[key] = value;
			if (parseInt(key) == key && key > this.___key_int) {
				this.___key_int = parseInt(key) + 1;
			}
		};
		HashArray.prototype.push = function (value) {
			var key = this.___key_int++
			this.___keys.push(key);
			this[key] = value;
		};
		HashArray.prototype.delete_ = function (key) {
			delete this[key];
			this.___keys.splice(this.___keys.indexOf(item), 1);
		};
		HashArray.prototype.data = function () {
			return this;
		};
		HashArray.prototype.count = function () {
			return this.___keys.length;
		};
		HashArray.prototype.current = function () {
			return this[this.___keys[this.___key_pos]];
		};
		HashArray.prototype.next = function () {
			this.___key_pos++;
		};
		HashArray.prototype.key = function () {
			return this.___keys[this.___key_pos];
		};
		HashArray.prototype.valid = function () {
			return typeof this[this.___keys[this.___key_pos]] != 'undefined';
		};
		return HashArray;
	})();
}
