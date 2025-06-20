var config = {
    map: {
        '*': {
            'jquery/compat/draggable': 'Laurensmedia_Productdesigner/js/jquery/compat/draggable',
            'jquery/compat/selectable': 'Laurensmedia_Productdesigner/js/jquery/compat/selectable',
            'jquery/compat/widget': 'Laurensmedia_Productdesigner/js/jquery/compat/widget',
            'jquery/compat/resizable': 'Laurensmedia_Productdesigner/js/jquery/compat/resizable',
            // Ajoutez ces dépendances supplémentaires
            'jquery/compat/sortable': 'Laurensmedia_Productdesigner/js/jquery/compat/sortable',
            'jquery/compat/droppable': 'Laurensmedia_Productdesigner/js/jquery/compat/droppable',
            'jquery/compat/slider': 'Laurensmedia_Productdesigner/js/jquery/compat/slider',
            'jquery/compat/button': 'Laurensmedia_Productdesigner/js/jquery/compat/button',
            'jquery/compat/dialog': 'Laurensmedia_Productdesigner/js/jquery/compat/dialog',
            'jquery/compat/menu': 'Laurensmedia_Productdesigner/js/jquery/compat/menu',
            'jquery/compat/tooltip': 'Laurensmedia_Productdesigner/js/jquery/compat/tooltip',
            'fabric': 'Laurensmedia_Productdesigner/js/fabric.min',
            'FancyProductDesigner': 'Laurensmedia_Productdesigner/js/FancyProductDesigner-all.min'
        }
    },
    shim: {
        'FancyProductDesigner': {
            deps: ['jquery', 'fabric'],
            exports: 'FancyProductDesigner'
        }
    },
    // Forcer le chargement de fabric-init avant tout autre script
    deps: ['fabric']
};