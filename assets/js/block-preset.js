(function(wp){
    const { __ } = wp.i18n;
    const { registerBlockType } = wp.blocks;
    const { PanelBody, ToggleControl, SelectControl, TextControl, Spinner } = wp.components;
    const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
    const ServerSideRender = wp.serverSideRender;
    const apiFetch = wp.apiFetch;
    const { useState, useEffect, useMemo } = wp.element;

    function usePresets(query) {
        const [items, setItems] = useState([]);
        const [loading, setLoading] = useState(false);
        const [error, setError] = useState(null);

        useEffect(() => {
            let active = true;
            async function run(){
                try {
                    setLoading(true);
                    setError(null);
                    // Query bpmgr_block_preset posts via REST
                    const params = new URLSearchParams();
                    params.set('per_page', '50');
                    if (query && query.search) params.set('search', query.search);
                    if (query && query.categoryId) params.set('bpmgr_block_category', query.categoryId);
                    if (query && query.tagId) params.set('bpmgr_block_tag', query.tagId);

                    const posts = await apiFetch({ path: '/wp/v2/bpmgr_block_preset?' + params.toString() });
                    if (!active) return;
                    setItems(posts || []);
                } catch (e) {
                    if (!active) return;
                    setError(e);
                } finally {
                    if (!active) return;
                    setLoading(false);
                }
            }
            run();
            return () => { active = false; };
        }, [query && query.search, query && query.categoryId, query && query.tagId]);

        return { items, loading, error };
    }

    function useTerms(taxonomy) {
        const [terms, setTerms] = useState([]);
        const [loading, setLoading] = useState(false);
        useEffect(() => {
            let active = true;
            async function run(){
                try {
                    setLoading(true);
                    const items = await apiFetch({ path: '/wp/v2/' + taxonomy + '?per_page=100&orderby=name&order=asc' });
                    if (!active) return;
                    setTerms(items || []);
                } catch (e) {
                    if (!active) return;
                    setTerms([]);
                } finally {
                    if (!active) return;
                    setLoading(false);
                }
            }
            run();
            return () => { active = false; };
        }, [taxonomy]);
        return { terms, loading };
    }

    registerBlockType('bpmgr/block-preset', {
        title: __('Block Preset', 'block-presets-manager'),
        icon: 'block-default',
        category: 'widgets',
        attributes: {
            presetId: { type: 'number' },
            presetTitle: { type: 'string', default: '' },
            showTitle: { type: 'boolean', default: false },
            className: { type: 'string', default: '' },
            align: { type: 'string' },
            categoryId: { type: 'number' },
            tagId: { type: 'number' },
            search: { type: 'string', default: '' },
        },
        supports: {
            align: [ 'wide', 'full' ],
            customClassName: true,
            anchor: true,
        },
        edit: (props) => {
            const { attributes, setAttributes } = props;
            const { presetId, presetTitle, showTitle, className, categoryId, tagId, search } = attributes;

            const query = useMemo(() => ({ categoryId, tagId, search }), [categoryId, tagId, search]);
            const { items, loading } = usePresets(query);
            const { terms: categories } = useTerms('bpmgr_block_category');
            const { terms: tags } = useTerms('bpmgr_block_tag');

            const blockProps = useBlockProps();

            const presetOptions = useMemo(() => {
                const options = [ { label: __('Select a preset…', 'block-presets-manager'), value: 0 } ];
                for (const item of items) {
                    options.push({ label: item.title && item.title.rendered ? item.title.rendered.replace(/<[^>]*>/g, '') : __('(no title)', 'block-presets-manager'), value: item.id });
                }
                return options;
            }, [items]);

            return (
                wp.element.createElement('div', blockProps,
                    wp.element.createElement(InspectorControls, null,
                        wp.element.createElement(PanelBody, { title: __('Preset', 'block-presets-manager'), initialOpen: true },
                            wp.element.createElement(TextControl, {
                                label: __('Search', 'block-presets-manager'),
                                value: search || '',
                                onChange: (v) => setAttributes({ search: v })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Filter by Category', 'block-presets-manager'),
                                value: categoryId || 0,
                                options: [{ label: __('All categories', 'block-presets-manager'), value: 0 }].concat((categories || []).map(t => ({ label: t.name, value: t.id }))),
                                onChange: (val) => setAttributes({ categoryId: parseInt(val, 10) || 0 })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Filter by Tag', 'block-presets-manager'),
                                value: tagId || 0,
                                options: [{ label: __('All tags', 'block-presets-manager'), value: 0 }].concat((tags || []).map(t => ({ label: t.name, value: t.id }))),
                                onChange: (val) => setAttributes({ tagId: parseInt(val, 10) || 0 })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Select Preset', 'block-presets-manager'),
                                value: presetId || 0,
                                options: presetOptions,
                                onChange: (val) => {
                                    const id = parseInt(val, 10) || 0;
                                    const found = items.find(p => p.id === id);
                                    setAttributes({ presetId: id, presetTitle: found ? (found.title && found.title.rendered ? found.title.rendered.replace(/<[^>]*>/g, '') : '') : '' });
                                }
                            }),
                            loading ? wp.element.createElement(Spinner, null) : null
                        ),
                        wp.element.createElement(PanelBody, { title: __('Options', 'block-presets-manager'), initialOpen: false },
                            wp.element.createElement(ToggleControl, {
                                label: __('Show title', 'block-presets-manager'),
                                checked: !!showTitle,
                                onChange: (v) => setAttributes({ showTitle: !!v })
                            }),
                            wp.element.createElement(TextControl, {
                                label: __('Additional CSS class(es)', 'block-presets-manager'),
                                value: className || '',
                                onChange: (v) => setAttributes({ className: v })
                            })
                        )
                    ),
                    presetId ? wp.element.createElement(ServerSideRender, {
                        block: 'bpmgr/block-preset',
                        attributes: attributes
                    }) : wp.element.createElement('div', { className: 'bpmgr-block-placeholder' }, __('Select a Block Preset from the sidebar.', 'block-presets-manager'))
                )
            );
        },
        save: () => null, // dynamic
    });
})(window.wp);


