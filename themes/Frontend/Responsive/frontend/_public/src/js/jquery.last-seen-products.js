;(function ($) {

    var emptyObj = {};

    /**
     * Shopware Last Seen Products Plugin
     *
     * This plugin creates a list of collected articles.
     * Those articles will be collected, when the user opens a detail page.
     * The created list will be showed as a product slider.
     */
    $.plugin('swLastSeenProducts', {
        
        defaults: {

            /**
             * Limit of the products showed in the slider
             *
             * @property productLimit
             * @type {Number}
             */
            productLimit: 20,

            /**
             * Base url used for uniquely identifying an article
             *
             * @property baseUrl
             * @type {String}
             */
            baseUrl: '/',

            /**
             * ID of the current shop used for uniquely identifying an article.
             *
             * @property shopId
             * @type {Number}
             */
            shopId: 1,

            /**
             * Article that will be added to the list when we are
             * on the detail page.
             *
             * @property currentArticle
             * @type {Object}
             */
            currentArticle: emptyObj,

            /**
             * Selector for the product list used for the product slider
             *
             * @property listSelector
             * @type {String}
             */
            listSelector: '.last-seen-products--slider',

            /**
             * Selector for the product slider container
             *
             * @property containerSelector
             * @type {String}
             */
            containerSelector: '.last-seen-products--container',

            /**
             * Class that will be used for a single product slider items
             *
             * @property itemCls
             * @type {String}
             */
            itemCls: 'last-seen-products--item product-slider--item product--box box--slider',

            /**
             * Class that will be used for the product title
             *
             * @property titleCls
             * @type {String}
             */
            titleCls: 'last-seen-products-item--title product--title',

            /**
             * Class that will be used for the product image
             *
             * @property imageCls
             * @type {String}
             */
            imageCls: 'last-seen-products-item--image product--image',

            /**
             * Picture source when no product image is available
             *
             * @property noPicture
             * @type {String}
             */
            noPicture: ''
        },

        /**
         * Initializes all necessary elements and collects the current
         * article when we are on the detail page.
         *
         * @public
         * @method init
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();

            me.$list = me.$el.find(me.opts.listSelector);
            me.$container = me.$list.find(me.opts.containerSelector);

            me.productSlider = me.$list.data('plugin_swProductSlider');

            if (!me.productSlider) {
                return;
            }

            me.storage = StorageManager.getLocalStorage();

            if ($('body').hasClass('is--ctl-detail')) {
                me.collectProduct(me.opts.currentArticle);
            }

            me.createProductList();
        },

        /**
         * Creates a list of all collected articles and calls
         * the product slider plugin.
         *
         * @public
         * @method createProductList
         */
        createProductList: function () {
            var me = this,
                opts = me.opts,
                itemKey = 'lastSeenProducts-' + opts.shopId + '-' + opts.baseUrl,
                productsJson = me.storage.getItem(itemKey),
                products = productsJson ? JSON.parse(productsJson) : [],
                len = Math.min(opts.productLimit, products.length),
                i = 0;

            if (len > 0) {
                me.$el.removeClass('is--hidden');
            }

            for (; i < len; i++) {
                me.$container.append(me.createTemplate(products[i]));
            }

            me.productSlider.initSlider();

            $.publish('plugin/swLastSeenProducts/onCreateProductList', [ me ]);
        },

        /**
         * Creates a product slider item template.
         *
         * @public
         * @method createTemplate
         * @param {Object} article
         */
        createTemplate: function (article) {
            var me = this,
                $template = $('<div>', {
                    'class': me.opts.itemCls,
                    'html': [
                        me.createProductImage(article),
                        me.createProductTitle(article)
                    ]
                });

            $.publish('plugin/swLastSeenProducts/onCreateTemplate', [ me, $template, article ]);

            return $template;
        },

        /**
         * Creates the product name title by the provided article data
         *
         * @public
         * @method createProductTitle
         * @param {Object} data
         */
        createProductTitle: function (data) {
            var me = this,
                $title = $('<a>', {
                    'rel': 'nofollow',
                    'class': me.opts.titleCls,
                    'title': data.articleName,
                    'href': data.linkDetailsRewritten,
                    'html': data.articleName
                });

            $.publish('plugin/swLastSeenProducts/onCreateProductTitle', [ me, $title, data ]);

            return $title;
        },

        /**
         * Creates a product image with all media queries for the
         * picturefill plugin
         *
         * @public
         * @method createProductImage
         * @param {Object} data
         */
        createProductImage: function (data) {
            var me = this,
                image = data.images[0],
                element,
                imageEl,
                imageMedia,
                srcSet;

            element = $('<a>', {
                'class': me.opts.imageCls,
                'href': data.linkDetailsRewritten,
                'title': data.articleName
            });

            imageEl = $('<span>', { 'class': 'image--element' }).appendTo(element);
            imageMedia = $('<span>', { 'class': 'image--media' }).appendTo(imageEl);

            if (image) {
                srcSet = image.sourceSet;
            } else {
                srcSet = me.opts.noPicture
            }

            $('<img>', {
                'srcset': srcSet,
                'alt': data.articleName,
                'title': data.articleName
            }).appendTo(imageMedia);

            $.publish('plugin/swLastSeenProducts/onCreateProductImage', [ me, element, data ]);

            return element;
        },

        /**
         * Adds a new article to the local storage for usage in the product slider.
         *
         * @public
         * @method collectProduct
         * @param {Object} newProduct
         */
        collectProduct: function (newProduct) {
            var me = this,
                opts = me.opts,
                itemKey = 'lastSeenProducts-' + opts.shopId + '-' + opts.baseUrl,
                productsJson = me.storage.getItem(itemKey),
                products = productsJson ? $.parseJSON(productsJson) : [],
                len = products.length,
                i = 0,
                url;

            if (!newProduct || $.isEmptyObject(newProduct)) {
                return;
            }

            for (; i < len; i++) {
                if (products[i].articleId === newProduct.articleId) {
                    newProduct = products.splice(i, 1)[0];
                    break;
                }
            }

            url = newProduct.linkDetailsRewritten;

            // Remove query string from article url
            if (url.indexOf('/sCategory') !== -1) {
                newProduct.linkDetailsRewritten = url.substring(0, url.indexOf('/sCategory'));
            } else if (url.indexOf('?') !== -1) {
                newProduct.linkDetailsRewritten = url.substring(0, url.indexOf('?'));
            }

            products.splice(0, 0, newProduct);

            while (products.length > opts.productLimit) {
                products.pop();
            }

            me.storage.setItem(itemKey, JSON.stringify(products));

            $.publish('plugin/swLastSeenProducts/onCollectProduct', [ me, newProduct ]);
        }
    });
}(jQuery));
