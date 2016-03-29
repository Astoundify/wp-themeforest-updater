<p>In order to receive automatic updates for your purchase please generate a personal token from ThemeForest.</p>

<p><a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank" class="button">Generate a Token</a></p>

<form action="post" name="marketify-updates-step" id="marketify-add-update-token">
	<p>
		<strong><label for="token"><?php _e( 'Personal Token:', 'marketify' ); ?></label></strong><br />
		<input name="token" value="<?php echo esc_attr( get_option( 'marketify_themeforest_updater_token', false ) ); ?>" name="token" style="width: 50%;" />
		<?php submit_button( __( 'Save Token', 'marketify' ), 'secondary', 'submit', false ); ?>
		<?php wp_nonce_field( 'marketify-add-token' ); ?>
	</p>
</form>

<script>
	jQuery(document).ready(function($) {
		$( '#marketify-add-update-token' ).on( 'submit', function(e) {
			e.preventDefault();

			console.log(ajaxurl);

			$form = $(this);

			var args = {
				action: 'marketify_set_token',
				token: $form.find( 'input[name=token]' ).val(),
				security: '<?php echo wp_create_nonce( 'marketify-add-token' ); ?>'
			};

			$.post( ajaxurl, args, function(response) {
				if ( response.success ) {
					$form.closest( $( '.not-completed' ).removeClass( 'not-completed' ).addClass( 'is-completed' ).text( 'Completed!' ) );
				}
			}, 'json' );
		});
	});
</script>
