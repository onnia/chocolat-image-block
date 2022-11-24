import assign from 'lodash.assign';

const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl } = wp.components;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Enable spacing control on the following blocks
const enableSpacingControlOnBlocks = [
	'core/image',
];

// Available spacing control options
const chocolatControlOptions = [
	{
		label: __( 'None' ),
		value: false,
	},
	{
		label: __( 'Chocolat wrapper' ),
		value: true,
	},
];

/**
 * Add spacing control attribute to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addSpacingControlAttribute = ( settings, name ) => {
	// Do nothing if it's another block than our defined ones.
	if ( ! enableSpacingControlOnBlocks.includes( name ) ) {
		return settings;
	}

	// Use Lodash's assign to gracefully handle if attributes are undefined
	settings.attributes = assign( settings.attributes, {
		chocolat: {
			type: 'boolean',
			default: false,
		},
	} );

	return settings;
};

addFilter( 'blocks.registerBlockType', 'chocolat-image-block/attribute/spacing', addSpacingControlAttribute );

/**
 * Create HOC to add spacing control to inspector controls of block.
 */
const withSpacingControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		// Do nothing if it's another block than our defined ones.
		if ( ! enableSpacingControlOnBlocks.includes( props.name ) ) {
			return (
				<BlockEdit { ...props } />
			);
		}

		// const { chocolat } = props.attributes.chocolat;
		// const chocolat = props.attributes.className === 'has-chocolat-wrapper' ? true : false;
		const { chocolat } = props.attributes;
	
		if ( chocolat ) {
			props.attributes.className = 'has-chocolat-wrapper';
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Apply Lightbox' ) }
						initialOpen={ true }
					>
						<SelectControl
							label={ __( 'Chocolat image' ) }
							value={ chocolat }
							options={ chocolatControlOptions }
							onChange={ ( selectedChocolatOption ) => {
								props.setAttributes( {
									chocolat: selectedChocolatOption === 'true' ? true : false,
									className: selectedChocolatOption === 'true' ? 'has-chocolat-wrapper' : '',
								} );
							} }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withSpacingControl' );

addFilter( 'editor.BlockEdit', 'chocolat-image-block/with-spacing-control', withSpacingControl );

/**
 * Add margin style attribute to save element of block.
 *
 * @param {object} saveElementProps Props of save element.
 * @param {Object} blockType Block type information.
 * @param {Object} attributes Attributes of block.
 *
 * @returns {object} Modified props of save element.
 */
const addSpacingExtraProps = ( saveElementProps, blockType, attributes ) => {
	// Do nothing if it's another block than our defined ones.
	if ( ! enableSpacingControlOnBlocks.includes( blockType.name ) ) {
		return saveElementProps;
	}

	if ( attributes.chocolat ) {
		// Use Lodash's assign to gracefully handle if attributes are undefined
		assign( saveElementProps, { chocolat: true } );
	}

	return saveElementProps;
};

addFilter( 'blocks.getSaveContent.extraProps', 'chocolat-image-block/get-save-content/extra-props', addSpacingExtraProps );
