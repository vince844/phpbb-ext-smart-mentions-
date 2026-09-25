(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		// Supporta sia l'editor classico (#message) che la Risposta Rapida (textarea[name="message"])
		// in prosilver, prosilver Special Edition e stili derivati
		var textareas = document.querySelectorAll('textarea#message, textarea[name="message"]');
		if (!textareas.length) return;

		var wrapper = document.createElement('div');
		wrapper.className = 'mention-autocomplete-dropdown';
		
		var dropdown = document.createElement('ul');
		wrapper.appendChild(dropdown);
		document.body.appendChild(wrapper);

		var currentTextarea = null;
		var isMentioning = false;
		var mentionQuery = '';
		var mentionAtIndex = -1;
		var mentionEndIndex = -1;
		var selectedIndex = -1;
		var suggestions = [];

		function onInput(e) {
			currentTextarea = this;
			var cursorPosition = currentTextarea.selectionStart;
			var textToCursor = currentTextarea.value.substring(0, cursorPosition);
			
			// Trova l'ultima occorrenza di @ che non sia preceduta da un carattere alfanumerico
			var match = textToCursor.match(/(^|[^a-zA-Z0-9])@([a-zA-Z0-9_\-\.]*)$/);

			if (match) {
				isMentioning = true;
				mentionQuery = match[2];
				mentionAtIndex = cursorPosition - mentionQuery.length - 1;
				mentionEndIndex = cursorPosition;
				
				if (mentionQuery.length >= 2) {
					fetchSuggestions(mentionQuery);
				} else {
					hideDropdown();
				}
			} else {
				hideDropdown();
			}
		}

		function onKeyDown(e) {
			if (!isMentioning || !wrapper.classList.contains('active')) return;

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
				renderDropdown();
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				selectedIndex = Math.max(selectedIndex - 1, 0);
				renderDropdown();
			} else if (e.key === 'Enter' || e.key === 'Tab') {
				if (selectedIndex >= 0 && selectedIndex < suggestions.length) {
					e.preventDefault();
					insertMention(suggestions[selectedIndex].username);
				}
			} else if (e.key === 'Escape') {
				hideDropdown();
			}
		}

		textareas.forEach(function(ta) {
			ta.addEventListener('input', onInput);
			ta.addEventListener('keydown', onKeyDown);
			ta.addEventListener('focus', function() {
				currentTextarea = this;
			});
		});

		// Chiusura dropdown se si clicca fuori dalla textarea o dal popup (SM-05)
		document.addEventListener('click', function(e) {
			if (wrapper.classList.contains('active')) {
				if (!wrapper.contains(e.target) && e.target !== currentTextarea) {
					hideDropdown();
				}
			}
		});

		function fetchSuggestions(query) {
			if (!window.phpbb_mention_url) return;
			
			fetch(window.phpbb_mention_url + '?q=' + encodeURIComponent(query), {
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(function(response) { return response.json(); })
				.then(function(data) {
					suggestions = data;
					if (suggestions.length > 0) {
						selectedIndex = 0;
						showDropdown();
					} else {
						hideDropdown();
					}
				})
				.catch(function(err) { hideDropdown(); });
		}

		// Helper function to get caret coordinates
		function getCaretCoordinates(element, position) {
			var div = document.createElement('div');
			var style = div.style;
			var computed = window.getComputedStyle(element);
			
			style.whiteSpace = 'pre-wrap';
			style.wordWrap = 'break-word';
			style.position = 'absolute';
			style.visibility = 'hidden';
			
			var properties = ['direction', 'boxSizing', 'width', 'height', 'overflowX', 'overflowY', 'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth', 'borderStyle', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'fontStyle', 'fontVariant', 'fontWeight', 'fontStretch', 'fontSize', 'fontSizeAdjust', 'lineHeight', 'fontFamily', 'textAlign', 'textTransform', 'textIndent', 'textDecoration', 'letterSpacing', 'wordSpacing', 'tabSize', 'MozTabSize'];
			properties.forEach(function (prop) {
				style[prop] = computed[prop];
			});
			
			div.textContent = element.value.substring(0, position);
			
			var span = document.createElement('span');
			span.textContent = element.value.substring(position) || '.';
			div.appendChild(span);
			
			document.body.appendChild(div);
			var coordinates = {
				top: span.offsetTop + parseInt(computed.borderTopWidth),
				left: span.offsetLeft + parseInt(computed.borderLeftWidth)
			};
			document.body.removeChild(div);
			
			return coordinates;
		}

		function showDropdown() {
			if (!currentTextarea || mentionAtIndex < 0) return;
			var rect = currentTextarea.getBoundingClientRect();
			var caretCoords = getCaretCoordinates(currentTextarea, mentionAtIndex);
			
			var topOffset = rect.top + window.scrollY + caretCoords.top + 20 - currentTextarea.scrollTop;
			var leftOffset = rect.left + window.scrollX + caretCoords.left;
			
			var maxLeft = window.innerWidth - 240;
			if (leftOffset > maxLeft) {
				leftOffset = Math.max(10, maxLeft);
			}

			wrapper.style.top = topOffset + 'px';
			wrapper.style.left = leftOffset + 'px';
			wrapper.classList.add('active');
			renderDropdown();
		}

		function hideDropdown() {
			wrapper.classList.remove('active');
			suggestions = [];
			selectedIndex = -1;
			isMentioning = false;
			mentionAtIndex = -1;
			mentionEndIndex = -1;
		}

		function renderDropdown() {
			dropdown.innerHTML = '';
			suggestions.forEach(function(user, index) {
				var li = document.createElement('li');
				li.textContent = user.username;
				if (index === selectedIndex) {
					li.className = 'selected';
				}
				li.addEventListener('mousedown', function(e) {
					e.preventDefault(); // Prevent focus loss
					insertMention(user.username);
				});
				dropdown.appendChild(li);
			});
		}

		function insertMention(username) {
			if (!currentTextarea || mentionAtIndex < 0) return;
			var currentText = currentTextarea.value;
			var beforeMention = currentText.substring(0, mentionAtIndex);
			var afterMention = currentText.substring(mentionEndIndex >= 0 ? mentionEndIndex : currentTextarea.selectionStart);
			
			// Costruisce la menzione garantendo sempre la presenza della chiocciola @ (SM-01)
			// Se lo username contiene spazi, lo avvolgiamo tra virgolette: @"Mario Rossi"
			var formattedName = username.indexOf(' ') !== -1 ? '"' + username + '"' : username;
			var mentionText = '@' + formattedName;
			
			var newText = beforeMention + mentionText + ' ' + afterMention;
			currentTextarea.value = newText;
			
			var newCursorPos = beforeMention.length + mentionText.length + 1;
			currentTextarea.setSelectionRange(newCursorPos, newCursorPos);
			
			hideDropdown();
			currentTextarea.focus();
		}
	});
})();
