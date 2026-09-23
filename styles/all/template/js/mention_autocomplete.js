(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		var textarea = document.getElementById('message');
		if (!textarea) return;

		var wrapper = document.createElement('div');
		wrapper.className = 'mention-autocomplete-dropdown';
		
		var dropdown = document.createElement('ul');
		wrapper.appendChild(dropdown);
		document.body.appendChild(wrapper);

		var isMentioning = false;
		var mentionQuery = '';
		var mentionStartIndex = -1;
		var selectedIndex = -1;
		var suggestions = [];

		textarea.addEventListener('input', function(e) {
			var cursorPosition = textarea.selectionStart;
			var textToCursor = textarea.value.substring(0, cursorPosition);
			
			// Trova l'ultima occorrenza di @ che non sia preceduta da un carattere alfanumerico
			var match = textToCursor.match(/(^|[^a-zA-Z0-9])@([a-zA-Z0-9_\-\.]*)$/);

			if (match) {
				isMentioning = true;
				mentionQuery = match[2];
				mentionStartIndex = cursorPosition - mentionQuery.length;
				
				if (mentionQuery.length >= 2) {
					fetchSuggestions(mentionQuery);
				} else {
					hideDropdown();
				}
			} else {
				isMentioning = false;
				hideDropdown();
			}
		});

		textarea.addEventListener('keydown', function(e) {
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
				isMentioning = false;
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
				.then(response => response.json())
				.then(data => {
					suggestions = data;
					if (suggestions.length > 0) {
						selectedIndex = 0;
						showDropdown();
					} else {
						hideDropdown();
					}
				})
				.catch(err => hideDropdown());
		}

		function showDropdown() {
			var rect = textarea.getBoundingClientRect();
			wrapper.style.left = rect.left + window.scrollX + 'px';
			// We approximate the top position to the top of the textarea + some offset
			wrapper.style.top = rect.top + window.scrollY + 25 + 'px';
			wrapper.style.left = rect.left + window.scrollX + 5 + 'px';
			wrapper.classList.add('active');
			renderDropdown();
		}

		function hideDropdown() {
			wrapper.classList.remove('active');
			suggestions = [];
			selectedIndex = -1;
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
			var currentText = textarea.value;
			var beforeMention = currentText.substring(0, mentionStartIndex);
			var afterMention = currentText.substring(textarea.selectionStart);
			
			// Se lo username contiene spazi, lo avvolgiamo tra virgolette
			var mentionText = username.indexOf(' ') !== -1 ? '"' + username + '"' : username;
			
			var newText = beforeMention + mentionText + ' ' + afterMention;
			textarea.value = newText;
			
			var newCursorPos = beforeMention.length + mentionText.length + 1;
			textarea.setSelectionRange(newCursorPos, newCursorPos);
			
			hideDropdown();
			isMentioning = false;
			textarea.focus();
		}
	});
})();
