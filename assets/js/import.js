(function($) {

	var running = false;

	/**
	 * Start import
	 */
	startImport = function() {

		if (running) {
			alert("Already running");
			return;
		}

		var data = $('[name="import[]"]:checked').map(function() {
						return $(this).val();
					}).get();

		$('.messages').html('');

		$.ajax({
			
			url : "import.php?cron=1&type=" + DisplayType,
			
			type : 'post',
			
			data : {
				'import' : data
			},
			
			complete : function() {
				listen();
			},
			
			success : function(data) {
				
				var out = "";
				
				if (data.error && data.error.length) {
					out += '<div class="alert alert-danger">' + data.error.join("<br />") + '</div>';
				}

				if (data.warning && data.warning.length) {
					out += '<div class="alert alert-warning">' + data.warning.join("<br />") + '</div>';
				}

				if (data.message && data.message.length) {
					out += '<div class="alert alert-success">' + data.message.join("<br />") + '</div>';
				}

				$('.messages').html(out);
			}
		});

		listen();

	};

	listen = function() {
		running = !running;
		$('#listen').val(running ? 'Stop' : 'Listen');
		update();
	};

	function update() {

		$.ajax({
			cache: false,
			url : "index.php?action=read&type=" + DisplayType,
			success : function(data) {

				if (DisplayType == 'text') {
					$('#log').val(data);
				} else {
					$('#log_html').html(data);						
				}
				if (running) {
					setTimeout(update, 0);
				}
			}
		});
	}
	
	$(window).ready(function() {
		update();
	});

	toggleImports = function(el, checked) {
		$(el).closest('table').find('input[type=checkbox]').attr('checked', checked);
	}

}(jQuery));

