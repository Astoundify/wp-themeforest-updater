# Astoundify ThemeForest Updater

Automatic updates from ThemeForest.net

## How it Works

Using a [Personal Token](https://build.envato.com/api/#token) a list of
purchases made with the corresponding Envato account are compared to the
actively installed themes. If a theme needs an update it will appear alongside
other themes in the standard WordPress update flow.

## Usage

This "plugin" does not implement itself by default. The `/updater` directory
should be place instead the theme, and a bootstrap file should be created in
order to set everything up.

You can see an example in the `/examples` directory.

### Credit

API code based on
[wp-envato-market](https://github.com/envato/wp-envato-market/blob/master/inc/api.php)
