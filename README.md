# OnceBlock – Build Once, Insert Anywhere

A free, open-source WordPress plugin to create and manage reusable Gutenberg block presets. Build content once and reuse it anywhere via a native block, PHP helpers, shortcodes, or REST API.

- Author: David Tavares Cuevas
- Plugin URI: https://github.com/neodavet/onceblock
- License: GPL v2 or later
- Requires: WordPress 5.9+, PHP 7.4+
- Tested up to: WordPress 7.1

## Key Features
- Native custom post type for presets: `bpmgr_block_preset`
- Gutenberg block to insert presets (`bpmgr/block-preset`)
- Shortcodes: `[bpmgr_block id="123"]`
- PHP helpers: `bpmgr_render_block_preset()`
- Taxonomies: categories (`bpmgr_block_category`) and tags (`bpmgr_block_tag`)
- Usage tracking: local table logs where presets are used
- REST API: list, fetch, and render presets
- Admin UI: Settings, Tools, and Statistics
- Optional ACF integration: auto-register ACF blocks from theme folders (only when enabled and ACF Pro is installed)
- Translation-ready and full uninstall cleanup

## Installation
1. Upload `onceblock/` to `wp-content/plugins/`.
2. Activate “OnceBlock” in Plugins.
3. Go to Settings → Blocks Presets to configure.
4. Create presets under the “Block Presets” menu and use the block or shortcodes in your content.

## Usage
### Gutenberg Block
- Insert “Block Preset” in the editor
- Use the sidebar to search/filter and select a preset
- Options: show title, custom CSS class, alignment

### PHP
```php
bpmgr_render_block_preset(123);
```

### Shortcodes
- `[bpmgr_block id="123"]`

### REST API
- `GET /wp-json/bpmgr/v1/block-presets`
- `GET /wp-json/bpmgr/v1/block-presets/{id}`
- `GET /wp-json/bpmgr/v1/block-presets/{id}/render`

## Settings Overview
Find these under Settings → Blocks Presets.

- Enable Block Presets: core `bpmgr_block_preset` functionality
- Enable ACF Blocks (Optional): auto-register ACF blocks from theme folders when ACF Pro is active
- ACF Block Folders (one per line):
  - `general/acf-blocks`
  - `public/acf-blocks`
  - `login-register/acf-blocks`
  - `members/acf-blocks`

## ACF Integration (Optional)
When enabled and ACF Pro is installed, the plugin can:
- Register ACF blocks from configured theme folders
- Provide a “Custom ACF Blocks” category in the editor
- Load per-block CSS/JS assets if present

If ACF is not installed or ACF features are disabled, the plugin works fully with native WordPress features.

## Troubleshooting
- ACF blocks not appearing:
  - Ensure ACF Pro is active and “Enable ACF Blocks” is ON
  - Verify folders exist and are configured in Settings
- Duplicate menus or conflicts:
  - Ensure no theme or other plugin registers the same post types

## Privacy
- The plugin creates a local table tracking preset usage (block ID, post ID, usage count, last used date)
- No personal data is collected by this feature and no data is sent to third parties
- Uninstalling from the Plugins screen removes plugin data (posts, options, tables)

## Contributing
Contributions are welcome! Open an issue or submit a pull request.
- Follow WordPress coding standards
- Keep user-facing strings translatable (`__()`, `_x()`) with text domain `onceblock`
- Add sanitization/escaping and capability checks where appropriate
- Include unminified sources for any bundled/minified assets

## License
GPL v2.0 or later. See `LICENSE` or:
https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt

## Credits
Created and maintained by David Tavares Cuevas. Learn more: https://neodavet.github.io/davetportfolio/
