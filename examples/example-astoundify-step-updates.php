<?php
/**
 */

$api = Astoundify_Envato_Market_API::instance();
?>

<p>In order to receive automatic updates for your purchase please generate a personal token from ThemeForest.</p>

<p><a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank" class="button">Generate a Token</a></p>

<p>Once generated, add the token below:</p>

<form action="post" name="marketify-updates-step" id="marketify-add-update-token">
	<p>
		<strong><label for="token"><?php _e( 'Personal Token:', 'marketify' ); ?></label></strong><br />
		<input name="token" value="<?php echo esc_attr( get_option( 'marketify_themeforest_updater_token', false ) ); ?>" name="token" style="width: 50%;" />
		<?php submit_button( __( 'Save Token', 'marketify' ), 'secondary', 'submit', false ); ?>
		<?php wp_nonce_field( 'marketify-add-token' ); ?>
	</p>
</form>

<p class="api-connection">API Connection: <strong><?php echo esc_attr( $api->connection_status_label() ); ?></strong></p>

<script>
	jQuery(document).ready(function($) {
		$( '#marketify-add-update-token' ).on( 'submit', function(e) {
			e.preventDefault();

			$form = $(this);

			var args = {
				action: 'marketify_set_token',
				token: $form.find( 'input[name=token]' ).val(),
				security: '<?php echo wp_create_nonce( 'marketify-add-token' ); ?>'
			};

			$.post( ajaxurl, args, function(response) {
				if ( response.success ) {
					$step = $( '#updates .section-title' );
					$status = $( '#updates .api-connection strong' );

					if ( response.data.can_request ) {
						$step.removeClass( 'not-completed' ).addClass( 'is-completed' );
					} else {
						$step.removeClass( 'is-completed' ).addClass( 'not-completed' );
					}

					$step.text( response.data.request_label );
					$status.text( response.data.request_label );
				}
			}, 'json' );
		});
	});
</script>
