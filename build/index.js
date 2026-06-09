(function(wp) {
    var __ = wp.i18n.__;
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var BlockControls = wp.blockEditor.BlockControls;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var JustifyToolbar = wp.blockEditor.JustifyToolbar;
    var PanelBody = wp.components.PanelBody;
    var ToggleControl = wp.components.ToggleControl;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var Button = wp.components.Button;
    var Spinner = wp.components.Spinner;
    var useEffect = wp.element.useEffect;
    var useState = wp.element.useState;
    var apiFetch = wp.apiFetch;
    var data = wp.data;
    var applyFilters = wp.hooks && wp.hooks.applyFilters
        ? wp.hooks.applyFilters
        : function(hookName, value) {
            return value;
        };
    var registerPlugin = wp.plugins && wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost && wp.editPost.PluginDocumentSettingPanel;
    var getBorderClassesAndStyles = wp.blockEditor.__experimentalGetBorderClassesAndStyles;
    var getColorClassesAndStyles = wp.blockEditor.__experimentalGetColorClassesAndStyles;
    var getSpacingClassesAndStyles = wp.blockEditor.__experimentalGetSpacingClassesAndStyles;
    var getTypographyClassesAndStyles = wp.blockEditor.getTypographyClassesAndStyles;

    function sideMenuBlockData() {
        return window.sideMenuBlock || {
            contentPosts: [],
            defaultContentId: 0,
            createContentUrl: '',
            canCreateContent: false,
            customizeUrl: '',
            classicCustomizerAvailable: false,
            restNamespace: 'sidemenu/v1',
            premiumBlockActive: false
        };
    }

    function isClassicMode(attributes) {
        return !attributes.mode || 'classic' === attributes.mode;
    }

    function buttonTitle(attributes) {
        if ('undefined' === typeof attributes.title) {
            return __('SideMenu', 'sidemenu');
        }

        return attributes.title;
    }

    function editorButtonTitle(attributes) {
        if ('' === buttonTitle(attributes) && (isClassicMode(attributes) || 'icon' !== (attributes.buttonIcon || 'title'))) {
            return __('SideMenu', 'sidemenu');
        }

        return buttonTitle(attributes);
    }

    function classNames() {
        return Array.prototype.slice.call(arguments).filter(Boolean).join(' ');
    }

    function mergeObjects() {
        return Object.assign.apply(Object, [{}].concat(Array.prototype.slice.call(arguments).filter(Boolean)));
    }

    function getButtonSupportProps(attributes) {
        var borderProps = getBorderClassesAndStyles ? getBorderClassesAndStyles(attributes) : {};
        var colorProps = getColorClassesAndStyles ? getColorClassesAndStyles(attributes) : {};
        var spacingProps = getSpacingClassesAndStyles ? getSpacingClassesAndStyles(attributes) : {};
        var typographyProps = getTypographyClassesAndStyles ? getTypographyClassesAndStyles(attributes) : {};

        return {
            className: classNames(
                'sidemenu-open-button',
                borderProps.className,
                colorProps.className,
                spacingProps.className,
                typographyProps.className
            ),
            style: mergeObjects(
                borderProps.style,
                colorProps.style,
                spacingProps.style,
                typographyProps.style
            )
        };
    }

    function editorButtonPreviewProps(attributes) {
        var props = isClassicMode(attributes) ? {} : getButtonSupportProps(attributes);

        props.className = classNames('sidemenu-editor-button-preview', props.className);

        if ('' === buttonTitle(attributes) && (isClassicMode(attributes) || 'icon' !== (attributes.buttonIcon || 'title'))) {
            props.style = mergeObjects(props.style, { color: '#757575' });
        }

        return props;
    }

    function menuIcon(attributes, context) {
        var icon = attributes.menuIcon || 'menu';
        var iconElement;

        if ('menu' === icon) {
            iconElement = el(
                'svg',
                {
                    className: 'sidemenu-button-icon',
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    width: '1em',
                    height: '1em',
                    'aria-hidden': 'true',
                    focusable: 'false'
                },
                el('path', { d: 'M5 5v1.5h14V5H5zm0 7.8h14v-1.5H5v1.5zM5 19h14v-1.5H5V19z' })
            );
        } else {
            iconElement = el(
                'svg',
                {
                    className: 'sidemenu-button-icon',
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    width: '1em',
                    height: '1em',
                    'aria-hidden': 'true',
                    focusable: 'false'
                },
                el('rect', { x: '4', y: '7.5', width: '16', height: '1.5' }),
                el('rect', { x: '4', y: '15', width: '16', height: '1.5' })
            );
        }

        return applyFilters(
            'sidemenu.openButtonIcon',
            iconElement,
            attributes,
            context || 'save'
        );
    }

    function buttonContents(attributes, context) {
        var icon = attributes.buttonIcon || 'title';
        var title = buttonTitle(attributes);
        var contents = [];

        if ('icon' === icon || 'left' === icon) {
            contents.push(menuIcon(attributes, context));
        }

        if ('icon' !== icon) {
            contents.push(el('span', { className: 'sidemenu-button-title' }, title));
        }

        if ('right' === icon) {
            contents.push(menuIcon(attributes, context));
        }

        return contents;
    }

    function buttonLinkProps(attributes) {
        var title = buttonTitle(attributes);
        var linkProps = getButtonSupportProps(attributes);

        linkProps.href = '#';

        if ('icon' === (attributes.buttonIcon || 'title')) {
            linkProps['aria-label'] = title || __('Open menu', 'sidemenu');
        }

        return linkProps;
    }

    function legacyButtonLinkProps(attributes) {
        var title = buttonTitle(attributes);
        var linkProps = { href: '#' };

        if ('icon' === (attributes.buttonIcon || 'title')) {
            linkProps['aria-label'] = title || __('Open menu', 'sidemenu');
        }

        return linkProps;
    }

    function blockClassName(attributes) {
        var justifyContent = attributes.justifyContent;

        return classNames(
            isClassicMode(attributes) ? 'open_sidemenu' : 'sidemenu-block-mode',
            !isClassicMode(attributes) && ('center' === justifyContent || 'right' === justifyContent)
                ? 'is-content-justification-' + justifyContent
                : ''
        );
    }

    function sidemenuButtonSave(attributes) {
        var saveProps = {
            className: blockClassName(attributes)
        };

        if (!isClassicMode(attributes)) {
            saveProps['data-sidemenu-mode'] = 'block';
        }

        return el(
            'div',
            wp.blockEditor.useBlockProps.save(saveProps),
            el(
                'a',
                isClassicMode(attributes) ? { href: '#' } : buttonLinkProps(attributes),
                isClassicMode(attributes) ? buttonTitle(attributes) : buttonContents(attributes)
            )
        );
    }

    function legacyBlockDivSave(attributes) {
        return el(
            'div',
            wp.blockEditor.useBlockProps.save({
                className: 'open_sidemenu sidemenu-block-mode',
                'data-sidemenu-mode': 'block'
            }),
            el('a', { href: '#' }, buttonTitle(attributes))
        );
    }

    function legacyClassicSpanSave(attributes) {
        return el(
            'span',
            { className: 'wp-block-sidemenu-sidemenu open_sidemenu' },
            el('a', { href: '#' }, buttonTitle(attributes))
        );
    }

    function legacyBlockSpanSave(attributes) {
        return el(
            'span',
            {
                className: 'wp-block-sidemenu-sidemenu open_sidemenu sidemenu-block-mode',
                'data-sidemenu-mode': 'block'
            },
            el('a', { href: '#' }, buttonTitle(attributes))
        );
    }

    function contentPostById(contentPosts, contentId) {
        var contentIdInt = parseInt(contentId, 10);

        return contentPosts.find(function(contentPost) {
            return parseInt(contentPost.id, 10) === contentIdInt;
        });
    }

    function contentOptions(contentPosts) {
        var options = [];

        contentPosts.forEach(function(contentPost) {
            options.push({
                label: contentPost.title,
                value: String(contentPost.id)
            });
        });

        return options;
    }

	    function contentControls(contentState, setContentState) {
        var selectedContent = contentPostById(contentState.contentPosts, contentState.defaultContentId);
        var controls = [];

        if (contentState.contentPosts.length > 1) {
            controls.push(
                el(SelectControl, {
                    label: __('Default SideMenu content', 'sidemenu'),
                    value: String(contentState.defaultContentId || 0),
                    options: contentOptions(contentState.contentPosts),
                    onChange: function(value) {
                        var contentId = parseInt(value, 10) || 0;
                        setContentState(Object.assign({}, contentState, { isSaving: true }));

                        apiFetch({
                            path: '/' + contentState.restNamespace + '/default-content',
                            method: 'POST',
                            data: { contentId: contentId }
                        }).then(function(response) {
                            setContentState(Object.assign({}, contentState, {
                                contentPosts: response.contentPosts,
                                defaultContentId: response.defaultContentId,
                                isSaving: false
                            }));
                        }).catch(function() {
                            setContentState(Object.assign({}, contentState, { isSaving: false }));
                        });
                    },
                    disabled: contentState.isSaving
                })
            );

            if (contentState.isSaving) {
                controls.push(el(Spinner));
            }
        }

        if (selectedContent) {
            if (selectedContent.editLink) {
                controls.push(el(Button, {
                    href: selectedContent.editLink,
                    variant: 'secondary'
                }, __('Edit SideMenu Content', 'sidemenu')));
            } else {
                controls.push(
                    el('p', null, __('You can use this SideMenu content, but an Editor or Administrator must edit it.', 'sidemenu'))
                );
            }
        } else if (!contentState.contentPosts.length) {
            if (contentState.canCreateContent && contentState.createContentUrl) {
                controls.push(el(Button, {
                    href: contentState.createContentUrl,
                    variant: 'primary'
                }, __('Add SideMenu Content', 'sidemenu')));
            } else {
                controls.push(
                    el('p', null, __('An Editor or Administrator must create SideMenu content before this block can use it.', 'sidemenu'))
                );
            }
        } else {
            controls.push(
                el('p', null, __('Choose the default SideMenu content used by free SideMenu blocks.', 'sidemenu'))
            );
        }

	        return controls;
	    }

	    function refreshClassicUsage(contentData, setContentData) {
	        apiFetch({
	            path: '/' + contentData.restNamespace + '/classic-usage'
	        }).then(function(response) {
	            setContentData(function(currentContentData) {
	                return Object.assign({}, currentContentData, {
	                    classicCustomizerAvailable: response.classicCustomizerAvailable || false,
	                    customizeUrl: response.customizeUrl || currentContentData.customizeUrl
	                });
	            });
	        }).catch(function() {
	            return;
	        });
	    }

	    function SideMenuContentSettingsPanel() {
	        var postType = data.useSelect(function(select) {
	            var editor = select('core/editor');

	            return editor && editor.getCurrentPostType ? editor.getCurrentPostType() : '';
	        }, []);
	        var meta = data.useSelect(function(select) {
	            var editor = select('core/editor');

	            return editor && editor.getEditedPostAttribute ? editor.getEditedPostAttribute('meta') || {} : {};
	        }, []);
	        var editorDispatch = data.useDispatch('core/editor');
	        var position = meta._sidemenu_position || 'right';

	        if ('sidemenu' !== postType || !editorDispatch || !editorDispatch.editPost) {
	            return null;
	        }

	        return el(
	            PluginDocumentSettingPanel,
	            {
	                name: 'sidemenu-panel-settings',
	                title: __('SideMenu Settings', 'sidemenu'),
	                initialOpen: true
	            },
	            el(SelectControl, {
	                label: __('Position', 'sidemenu'),
	                value: position,
	                options: [
	                    { label: __('Right', 'sidemenu'), value: 'right' },
	                    { label: __('Left', 'sidemenu'), value: 'left' }
	                ],
	                onChange: function(value) {
	                    var nextMeta = Object.assign({}, meta);

	                    nextMeta._sidemenu_position = value;
	                    editorDispatch.editPost({ meta: nextMeta });
	                }
	            })
	        );
	    }

	    if (registerPlugin && PluginDocumentSettingPanel && data && data.useSelect && data.useDispatch) {
	        registerPlugin('sidemenu-content-settings', {
	            render: SideMenuContentSettingsPanel
	        });
	    }

    wp.blocks.registerBlockType('sidemenu/sidemenu', {
        icon: el(
            'svg',
            {
                xmlns: 'http://www.w3.org/2000/svg',
                viewBox: '0 0 24 24',
                'aria-hidden': 'true',
                focusable: 'false'
            },
            el('path', { d: 'M5 5v1.5h14V5H5zm0 7.8h14v-1.5H5v1.5zM5 19h14v-1.5H5V19z' })
        ),
        attributes: {
            title: {
                type: 'string',
                default: __('SideMenu', 'sidemenu')
            },
            mode: {
                type: 'string'
            },
            contentId: {
                type: 'number'
            },
            buttonIcon: {
                type: 'string',
                default: 'title'
            },
            menuIcon: {
                type: 'string',
                default: 'menu'
            },
            justifyContent: {
                type: 'string'
            },
            style: {
                type: 'object',
                default: {
                    spacing: {
                        padding: {
                            top: 'var:preset|spacing|20',
                            right: 'var:preset|spacing|20',
                            bottom: 'var:preset|spacing|20',
                            left: 'var:preset|spacing|20'
                        }
                    }
                }
            }
        },
        supports: {
            html: false,
            align: false,
            layout: false,
            color: {
                __experimentalSkipSerialization: true,
                text: true,
                background: true,
                gradients: true,
                link: true
            },
            typography: {
                __experimentalSkipSerialization: [
                    'fontSize',
                    'lineHeight',
                    'fontFamily',
                    'fontStyle',
                    'fontWeight',
                    'letterSpacing',
                    'textDecoration',
                    'textTransform'
                ],
                fontSize: true,
                lineHeight: true,
                fontFamily: true,
                fontStyle: true,
                fontWeight: true,
                letterSpacing: true,
                textDecoration: true,
                textTransform: true
            },
            spacing: {
                __experimentalSkipSerialization: true,
                padding: true
            },
            dimensions: {
                minHeight: true
            },
            border: {
                __experimentalSkipSerialization: true,
                color: true,
                radius: true,
                style: true,
                width: true
            }
        },
        selectors: {
            root: '.wp-block-sidemenu-sidemenu .sidemenu-open-button'
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var initialData = sideMenuBlockData();
            var contentState = useState({
                contentPosts: initialData.contentPosts || [],
                defaultContentId: initialData.defaultContentId || 0,
                createContentUrl: initialData.createContentUrl || '',
                canCreateContent: initialData.canCreateContent || false,
                restNamespace: initialData.restNamespace || 'sidemenu/v1',
                premiumBlockActive: initialData.premiumBlockActive || false,
                classicCustomizerAvailable: initialData.classicCustomizerAvailable || false,
                customizeUrl: initialData.customizeUrl || '',
                isSaving: false
            });
            var contentData = contentState[0];
            var setContentData = contentState[1];

	            useEffect(function() {
	                if ('undefined' === typeof attributes.mode) {
	                    setAttributes({ mode: 'block' });
	                }
	            }, []);

	            var classicMode = isClassicMode(attributes);

	            useEffect(function() {
	                var wasSaving = false;
	                var unsubscribe;

	                if (!classicMode || contentData.classicCustomizerAvailable || !data || !data.subscribe || !data.select) {
	                    return;
	                }

	                unsubscribe = data.subscribe(function() {
	                    var editor = data.select('core/editor');
	                    var isSaving;
	                    var isAutosaving;

	                    if (!editor || !editor.isSavingPost) {
	                        return;
	                    }

	                    isSaving = !!editor.isSavingPost();
	                    isAutosaving = editor.isAutosavingPost && editor.isAutosavingPost();

	                    if (wasSaving && !isSaving && !isAutosaving) {
	                        refreshClassicUsage(contentData, setContentData);
	                    }

	                    wasSaving = isSaving;
	                });

	                return function() {
	                    if (unsubscribe) {
	                        unsubscribe();
	                    }
	                };
	            }, [classicMode, contentData.classicCustomizerAvailable, contentData.restNamespace]);

	            var blockProps = useBlockProps({
	                className: blockClassName(attributes)
	            });
            var showClassicModeToggle = classicMode || contentData.classicCustomizerAvailable;
            var panelControls = [
                el(TextControl, {
                    label: __('Open Button Title', 'sidemenu'),
                    value: attributes.title,
                    onChange: function(title) {
                        var nextAttributes = { title: title };

                        if (!classicMode && '' === title) {
                            nextAttributes.buttonIcon = 'icon';
                        }

                        setAttributes(nextAttributes);
                    }
                })
            ];

            if (showClassicModeToggle) {
                panelControls.push(el(ToggleControl, {
                    __nextHasNoMarginBottom: true,
                    label: __('Classic Mode', 'sidemenu'),
                    help: classicMode
                        ? (
                            contentData.classicCustomizerAvailable
                                ? el('a', { href: contentData.customizeUrl }, __('Uses the Customizer SideMenu settings.', 'sidemenu'))
                                : __('Save this post or template before using the Customizer SideMenu settings.', 'sidemenu')
                        )
                        : __('Uses SideMenu block content.', 'sidemenu'),
                    checked: classicMode,
                    onChange: function(checked) {
                        setAttributes({ mode: checked ? 'classic' : 'block' });
                    }
                }));
            }

            if (!classicMode) {
                panelControls = panelControls.concat(
                    [
                        el(SelectControl, {
                            label: __('Open Button Display', 'sidemenu'),
                            value: attributes.buttonIcon || 'title',
                            options: [
                                { label: __('Title only', 'sidemenu'), value: 'title' },
                                { label: __('Icon', 'sidemenu'), value: 'icon' },
                                { label: __('Icon to the left of title', 'sidemenu'), value: 'left' },
                                { label: __('Icon to the right of title', 'sidemenu'), value: 'right' }
                            ],
                            onChange: function(buttonIcon) {
                                setAttributes({ buttonIcon: buttonIcon });
                            }
                        }),
                        !contentData.premiumBlockActive && 'title' !== (attributes.buttonIcon || 'title') && el(SelectControl, {
                            label: __('Hamburger Icon', 'sidemenu'),
                            value: attributes.menuIcon || 'menu',
                            options: [
                                { label: __('Two lines', 'sidemenu'), value: 'handle' },
                                { label: __('Three lines', 'sidemenu'), value: 'menu' }
                            ],
                            onChange: function(menuIconValue) {
                                setAttributes({ menuIcon: menuIconValue });
                            }
                        })
                    ],
                    contentControls(contentData, setContentData)
                );
            }

            return el(
                wp.element.Fragment,
                null,
                !classicMode && el(
                    BlockControls,
                    null,
                    el(JustifyToolbar, {
                        allowedControls: ['left', 'center', 'right'],
                        value: attributes.justifyContent || 'left',
                        onChange: function(justifyContent) {
                            setAttributes({
                                justifyContent: !justifyContent || 'left' === justifyContent ? undefined : justifyContent
                            });
                        }
                    })
                ),
                el(
                    'div',
                    blockProps,
                    el(
                        'span',
                        editorButtonPreviewProps(attributes),
                        classicMode
                            ? editorButtonTitle(attributes)
                            : buttonContents(Object.assign({}, attributes, { title: editorButtonTitle(attributes) }), 'editor')
                    )
                ),
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: __('SideMenu Open Button', 'sidemenu'), initialOpen: true },
                        panelControls
                    )
                )
            );
        },

        save: function(props) {
            return sidemenuButtonSave(props.attributes);
        },

        deprecated: [
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: __('SideMenu', 'sidemenu')
                    },
                    mode: {
                        type: 'string'
                    },
                    contentId: {
                        type: 'number'
                    },
                    buttonIcon: {
                        type: 'string',
                        default: 'title'
                    },
                    menuIcon: {
                        type: 'string',
                        default: 'menu'
                    },
                    style: {
                        type: 'object'
                    }
                },
                supports: {
                    html: false,
                    align: false,
                    layout: false,
                    color: {
                        __experimentalSkipSerialization: true,
                        text: true,
                        background: true,
                        gradients: true,
                        link: true
                    },
                    typography: {
                        __experimentalSkipSerialization: [
                            'fontSize',
                            'lineHeight',
                            'fontFamily',
                            'fontStyle',
                            'fontWeight',
                            'letterSpacing',
                            'textDecoration',
                            'textTransform'
                        ],
                        fontSize: true,
                        lineHeight: true,
                        fontFamily: true,
                        fontStyle: true,
                        fontWeight: true,
                        letterSpacing: true,
                        textDecoration: true,
                        textTransform: true
                    },
                    spacing: {
                        __experimentalSkipSerialization: true,
                        padding: true
                    },
                    dimensions: {
                        minHeight: true
                    },
                    border: {
                        __experimentalSkipSerialization: true,
                        color: true,
                        radius: true,
                        style: true,
                        width: true
                    }
                },
                save: function(props) {
                    return el(
                        'div',
                        wp.blockEditor.useBlockProps.save({
                            className: 'open_sidemenu sidemenu-block-mode',
                            'data-sidemenu-mode': 'block'
                        }),
                        el('a', buttonLinkProps(props.attributes), buttonContents(props.attributes))
                    );
                },
                isEligible: function(attributes) {
                    return 'block' === attributes.mode;
                },
                migrate: function(attributes) {
                    return Object.assign({}, attributes, {
                        title: buttonTitle(attributes),
                        mode: 'block',
                        buttonIcon: attributes.buttonIcon || 'title',
                        menuIcon: attributes.menuIcon || 'menu'
                    });
                }
            },
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: __('SideMenu', 'sidemenu')
                    },
                    mode: {
                        type: 'string'
                    },
                    contentId: {
                        type: 'number'
                    },
                    buttonIcon: {
                        type: 'string',
                        default: 'title'
                    },
                    menuIcon: {
                        type: 'string',
                        default: 'menu'
                    }
                },
                save: function(props) {
                    return el(
                        'div',
                        wp.blockEditor.useBlockProps.save({
                            className: 'open_sidemenu sidemenu-block-mode',
                            'data-sidemenu-mode': 'block'
                        }),
                        el('a', legacyButtonLinkProps(props.attributes), buttonContents(props.attributes))
                    );
                },
                isEligible: function(attributes) {
                    return 'block' === attributes.mode;
                },
                migrate: function(attributes) {
                    return {
                        title: buttonTitle(attributes),
                        mode: 'block',
                        buttonIcon: attributes.buttonIcon || 'title',
                        menuIcon: attributes.menuIcon || 'menu'
                    };
                }
            },
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: __('SideMenu', 'sidemenu')
                    },
                    mode: {
                        type: 'string'
                    },
                    contentId: {
                        type: 'number'
                    }
                },
                save: function(props) {
                    return legacyBlockDivSave(props.attributes);
                },
                isEligible: function(attributes) {
                    return 'block' === attributes.mode;
                },
                migrate: function(attributes) {
                    return {
                        title: buttonTitle(attributes),
                        mode: 'block',
                        buttonIcon: 'title',
                        menuIcon: 'menu'
                    };
                }
            },
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: __('SideMenu', 'sidemenu')
                    },
                    mode: {
                        type: 'string'
                    }
                },
                save: function(props) {
                    return legacyBlockSpanSave(props.attributes);
                },
                isEligible: function(attributes) {
                    return 'block' === attributes.mode;
                },
                migrate: function(attributes) {
                    return {
                        title: buttonTitle(attributes),
                        mode: 'block',
                        buttonIcon: 'title',
                        menuIcon: 'menu'
                    };
                }
            },
            {
                attributes: {
                    title: {
                        type: 'string',
                        default: __('SideMenu', 'sidemenu')
                    }
                },
                save: function(props) {
                    return legacyClassicSpanSave(props.attributes);
                },
                isEligible: function(attributes) {
                    return !attributes.mode;
                },
                migrate: function(attributes) {
                    return {
                        title: buttonTitle(attributes),
                        mode: 'classic'
                    };
                }
            }
        ]
    });
})(window.wp);
