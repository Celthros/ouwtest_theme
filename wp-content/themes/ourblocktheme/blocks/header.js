wp.blocks.registerBlockType('ourblocktheme/header', {
  title: 'OUR Header',
  supports: {
    align: ['full'],
  },
  attributes: {
    align: {type: 'string', default: 'full'},
  },
  edit: function () {
    return wp.element.createElement(
      'div',
      { className: 'our-placeholder-block' },
      'Header Placeholder'
    );
  },
  save: function () {
    return null;
  },
});
