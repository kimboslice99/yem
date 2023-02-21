	braintree.dropin.create({
	  // Step three: get client token from your server, such as via
	 //    templates or async http request
	  authorization: "<?= $clientToken?>",
	  container: '#dropin-container'
	}, (error, dropinInstance) => {
	  // Use `dropinInstance` here
	  // Methods documented at https://braintree.github.io/braintree-web-drop-in/docs/current/Dropin.html
	});