'use strict';

class SPUIOnboarding {
	root;
	onboardingRoot;
	onboardingForm;
	settings;
	isSubmitting = false;

	constructor(data) {
		this.root = data.root;
		this.onboardingRoot = this.root.querySelector('#tab-nokey');
		this.onboardingForm = (this.onboardingRoot !== null) ? this.onboardingRoot.closest('form') : null;
		this.settings = data.settings || null;
		this.Init();
	}

	Init() {
		if (this.onboardingRoot !== null) {
			this.onboardingRoot.dataset.spuiOnboardingReady = '1';
		}

		this.InitActions();
	}

	InitActions() {
		if (this.onboardingRoot === null) {
			return;
		}

		this.InitNewKeySwitch();

		var addButton = this.onboardingRoot.querySelector('button[name="add-key"]');
		if (addButton !== null) {
			addButton.addEventListener('click', this.AddKeyEvent.bind(this));
		}

		let inputs = ['pluginemail', 'new-key'];
		for (let i = 0; i < inputs.length; i++) {
			var input = document.getElementById(inputs[i]);
			if (input !== null) {
				input.addEventListener('keypress', this.EnterKeyPressEvent.bind(this));
				input.addEventListener('focus', this.ActivateInputPanelEvent.bind(this));
			}
		}
	}

	InitNewKeySwitch() {
		var panels = this.onboardingRoot.querySelectorAll('.onboarding-join-wrapper settinglist');
		for (var i = 0; i < panels.length; i++) {
			panels[i].addEventListener('click', this.NewKeyPanelEvent.bind(this));
			panels[i].addEventListener('keydown', this.NewKeyPanelKeyEvent.bind(this));
		}
	}

	NewKeyPanelKeyEvent(event) {
		if (event.key !== 'Enter' && event.key !== ' ') {
			return;
		}

		event.preventDefault();
		this.NewKeyPanelEvent(event);
	}

	EnterKeyPressEvent(event) {
		if (event.keyCode === 13) {
			event.preventDefault();
			this.AddKeyEvent(event);
			return false;
		}
	}

	ActivateInputPanelEvent(event) {
		var panel = event.target.closest('settinglist');
		if (panel !== null) {
			this.ActivatePanel(panel);
		}
	}

	NewKeyPanelEvent(event) {
		let target = event.target;
		if (event.target.tagName.toLowerCase() !== 'settinglist') {
			target = event.target.closest('settinglist');
		}

		if (target === null || target.classList.contains('now-active')) {
			return true;
		}

		var panels = this.onboardingRoot.querySelectorAll('.onboarding-join-wrapper settinglist');
		this.ActivatePanel(target);
		return true;
	}

	ActivatePanel(target) {
		var panels = this.onboardingRoot.querySelectorAll('.onboarding-join-wrapper settinglist');
		for (var i = 0; i < panels.length; i++) {
			panels[i].classList.remove('now-active');
			panels[i].setAttribute('aria-pressed', 'false');
		}

		target.classList.add('now-active');
		target.setAttribute('aria-pressed', 'true');
	}

	AddKeyEvent(event) {
		event.preventDefault();

		if (this.isSubmitting) {
			return false;
		}

		var activePanel = this.onboardingRoot.querySelector('settinglist.now-active');
		var existingPanel = this.onboardingRoot.querySelector('settinglist.existing-customer');
		var apiKeyInput = (existingPanel !== null) ? existingPanel.querySelector('input[name="login_apiKey"]') : null;

		if (apiKeyInput !== null && apiKeyInput.value.trim().length > 0) {
			activePanel = existingPanel;
			this.ActivatePanel(existingPanel);
		}

		var nonceInput = (this.onboardingForm !== null) ? this.onboardingForm.querySelector('input[name="sp-nonce"]') : null;

		if (activePanel === null || nonceInput === null) {
			this.ShowSubmitError('The onboarding form is incomplete. Please refresh the page and try again.');
			return false;
		}

		var formData = new FormData();
		var submit = true;

		formData.append('sp-nonce', nonceInput.value);

		if (activePanel.classList.contains('new-customer')) {
			let email = activePanel.querySelector('input[name="pluginemail"]');
			let tos = activePanel.querySelector('input[name="tos"]');

			email.classList.remove('invalid');
			tos.classList.remove('invalid');

			if (false === this.IsEmailValid(email.value)) {
				email.classList.add('invalid');
				activePanel.querySelector('#pluginemail-error').style.display = 'block';
				submit = false;
			}

			if (false === tos.checked) {
				tos.classList.add('invalid');
				jQuery('.tos-robo').fadeIn(400, function () { jQuery('.tos-hand').fadeIn(); });
				submit = false;
			}
			else {
				formData.append(email.name, email.value);
				formData.append('screen_action', 'action_request_new_key');
			}
		}
		else if (activePanel.classList.contains('existing-customer')) {
			let apiKey = activePanel.querySelector('input[name="login_apiKey"]');
			apiKey.classList.remove('invalid');
			if (apiKey.value.trim().length === 0) {
				apiKey.classList.add('invalid');
				this.ShowSubmitError('Please enter your API key before continuing.');
				submit = false;
			}
			formData.append('apiKey', apiKey.value);
			formData.append('screen_action', 'action_addkey');
		}

		if (true === submit) {
			let button = this.onboardingRoot.querySelector('.onboard-submit button');
			this.isSubmitting = true;
			button.classList.add('submitting');
			this.DoAjaxRequest(formData)
				.then((json) => {
					this.FormAddKeyResponse(json);
				})
				.catch((error) => {
					this.ResetSubmitButton();
					this.ShowSubmitError(error.message || 'The request failed. Please refresh the page and try again.');
				});
		}
	}

	FormAddKeyResponse(json) {
		var anchor = this.onboardingRoot.querySelector('.submit-errors');
		anchor.innerHTML = '';

		this.ResetSubmitButton();

		if (!json) {
			this.ShowSubmitError('The server returned an empty response. Please refresh the page and try again.');
			return;
		}

		if (json.display_notices) {
			for (let i = 0; i < json.display_notices.length; i++) {
				anchor.innerHTML += json.display_notices[i];
			}

			anchor.classList.add('is-visible');
		}

		window.setTimeout(function () {
			if (json.redirect) {
				if (json.redirect == 'reload') {
					window.location.reload();
				}
				else {
					window.location.href = json.redirect;
				}
			}
		}, 3000);
	}

	ResetSubmitButton() {
		this.isSubmitting = false;

		let button = this.onboardingRoot.querySelector('.onboard-submit button');
		if (button !== null) {
			button.classList.remove('submitting');
		}
	}

	ShowSubmitError(message) {
		var anchor = this.onboardingRoot.querySelector('.submit-errors');
		if (anchor === null) {
			return;
		}

		anchor.innerHTML = '<div class="notice notice-error is_ajax"><p>' + this.EscapeHtml(message) + '</p></div>';
		anchor.classList.add('is-visible');
	}

	EscapeHtml(string) {
		var node = document.createElement('div');
		node.textContent = string;
		return node.innerHTML;
	}

	IsEmailValid(email) {
		var regex = /^\S+@\S+\.\S+$/;
		return regex.test(email);
	}

	async DoAjaxRequest(formData) {
		formData.append('action', 'spui_settingsRequest');
		formData.append('ajaxSave', 'true');
		formData.append('request_url', window.location.toString());

		if (false === formData.has('nonce') && typeof SPUIOnboardingData !== 'undefined') {
			formData.append('nonce', SPUIOnboardingData.nonceSettingsRequest);
		}
		else if (false === formData.has('nonce') && typeof SPUIProcessorData !== 'undefined') {
			formData.append('nonce', SPUIProcessorData.nonce_settingsrequest);
		}

		const ajaxUrl = this.GetAjaxUrl();
		if (!ajaxUrl) {
			throw new Error('The onboarding AJAX URL is missing. Please refresh the page and try again.');
		}

		const response = await fetch(ajaxUrl, {
			method: 'POST',
			body: formData
		});

		if (!response.ok) {
			throw new Error('The server rejected the onboarding request.');
		}

		return response.json();
	}

	GetAjaxUrl() {
		if (typeof SPUIOnboardingData !== 'undefined' && SPUIOnboardingData.ajaxUrl) {
			return SPUIOnboardingData.ajaxUrl;
		}

		if (typeof SPUI !== 'undefined' && SPUI.AJAX_URL) {
			return SPUI.AJAX_URL;
		}

		if (typeof ajaxurl !== 'undefined') {
			return ajaxurl;
		}

		return null;
	}
}

function spuiBootOnboarding(data) {
	if (!data || !data.root) {
		return;
	}

	var onboardingRoot = data.root.querySelector('#tab-nokey');
	if (onboardingRoot === null || onboardingRoot.dataset.spuiOnboardingReady === '1') {
		return;
	}

	new SPUIOnboarding(data);
}

document.addEventListener('shortpixel.settings.loaded', function (event) {
	spuiBootOnboarding(event.detail);
});

document.addEventListener('DOMContentLoaded', function () {
	var root = document.querySelector('.wrap.is-shortpixel-settings-page');
	if (root === null) {
		return;
	}

	spuiBootOnboarding({
		root: root,
		settings: null
	});
});
