<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The default title of your admin panel, this goes into the title tag
    | of your page. You can override it per page with the title section.
    | You can optionally also specify a title prefix and/or postfix.
    |
    */

    'title' => 'Inventory System',

    'title_prefix' => '',

    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | This logo is displayed at the upper left corner of your admin panel.
    | You can use basic HTML here if you want. The logo has also a mini
    | variant, used for the mini side bar. Make it 3 letters or so
    |
    */

    'logo' => '<b>Inventory&nbsp System</b>',

    'logo_mini' => '<b>GUI</b>',

    /*
    |--------------------------------------------------------------------------
    | Skin Color
    |--------------------------------------------------------------------------
    |
    | Choose a skin color for your admin panel. The available skin colors:
    | blue, black, purple, yellow, red, and green. Each skin also has a
    | ligth variant: blue-light, purple-light, purple-light, etc.
    |
    */

    'skin' => 'blue-light',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Choose a layout for your admin panel. The available layout options:
    | null, 'boxed', 'fixed', 'top-nav'. null is the default, top-nav
    | removes the sidebar and places your menu in the top navbar
    |
    */

    'layout' => 'fixed',

    /*
    |--------------------------------------------------------------------------
    | Collapse Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we choose and option to be able to start with a collapsed side
    | bar. To adjust your sidebar layout simply set this  either true
    | this is compatible with layouts except top-nav layout option
    |
    */

    'collapse_sidebar' => false,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Register here your dashboard, logout, login and register URLs. The
    | logout URL automatically sends a POST request in Laravel 5.3 or higher.
    | You can set the request to a GET or POST with logout_method.
    | Set register_url to null if you don't want a register link.
    |
    */

    'dashboard_url' => 'home',

    'logout_url' => 'logout',

    'logout_method' => null,

    'login_url' => 'login',

    'register_url' => 'register',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Specify your menu items to display in the left sidebar. Each menu item
    | should have a text and and a URL. You can also specify an icon from
    | Font Awesome. A string instead of an array represents a header in sidebar
    | layout. The 'can' is a filter on Laravel's built in Gate functionality.
    |
    */

    'menu' => [
        'MAIN NAVIGATION',

        [
            'text' => 'Setup',
            'icon' => 'wrench',
            'permission' => 'read-setup',
            'submenu' => [
                [
                    'text' => 'Company',
                    'url'  => 'admin/company',
                    'icon' => 'group',
                    'permission' => 'read-company',
                ],

                [
                    'text' => 'Company 1',
                    'url'  => 'admin/company1',
                    'icon' => 'group',
                    'permission' => 'read-company',
                ],

                [
                    'text' => 'Lokasi',
                    'url'  => 'admin/masterlokasi',
                    'icon' => 'map-marker',
                    'permission'  => 'read-lokasi',
                ],

                [
                    'text'        => 'Catatan Khusus PO',
                    'url'         => 'admin/catatanpo',
                    'icon'        => 'sticky-note',
                    'permission' => 'read-catatanpo',
                ],

                [
                    'text'        => 'No Transaksi Setup',
                    'url'         => 'admin/transaksisetup',
                    'icon'        => 'tags',
                    'permission' => 'read-transaksisetup',
                ],

                [
                    'text'        => 'Tax Setup',
                    'url'         => 'admin/taxsetup',
                    'icon'        => 'line-chart',
                    'permission' => 'read-taxsetup',
                ],

                [
                    'text'        => 'Signature',
                    'url'         => 'admin/signature',
                    'icon'        => 'pencil-square',
                    'permission' => 'read-signature',
                ],
                
                [
                    'text'        => 'Setup Akses Transaksi',
                    'url'         => 'admin/setupakses',
                    'icon'        => 'group',
                ],
                
                [
                    'text'        => 'Setup Folder',
                    'url'         => 'admin/setupfolder',
                    'icon'        => 'group',
                ],
            ]
        ],

        [
            'text' => 'Master Data',
            'icon' => 'server',
            'permission' => 'read-masterdata',
            'submenu' => [
                // [
                //     'text' => 'Katalog',
                //     'url'  => 'admin/katalog',
                //     'icon' => 'columns',
                //     'permission'  => 'read-katalog',
                // ],

                // [
                //     'text' => 'Lokasi Rak',
                //     'url'  => 'admin/lokasirak',
                //     'icon' => 'tags',
                //     'permission'  => 'read-lokasirak',
                // ],  

                [
                    'text' => 'Jasa',
                    'url'  => 'admin/jasa',
                    'icon' => 'male',
                    'permission'  => 'read-jasa',
                    
                ],
                
                [
                    'text' => 'Non-Stock',
                    'url'  => 'admin/nonstock',
                    'icon' => 'ban',
                    'permission'  => 'read-nonstock',
                    
                ],

                [
                    'text' => 'Alat',
                    'url'  => 'admin/alat',
                    'icon' => 'cog',
                    'permission'  => 'read-alat',
                ],

                 [
                    'text' => 'Kapal',
                    'url'  => 'admin/kapal',
                    'icon' => 'ship',
                    'permission'  => 'read-kapal',
                ],

                [
                    'text' => 'Jenis Mobil',
                    'url'  => 'admin/jenismobil',
                    'icon' => 'road',
                    'permission'  => 'read-jenismobil',
                ],

                [
                    'text' => 'Mobil',
                    'url'  => 'admin/mobil',
                    'icon' => 'car',
                    'permission'  => 'read-mobil',
                ],

                [
                    'text' => 'Vendor',
                    'url'  => 'admin/vendor',
                    'icon' => 'truck',
                    'permission'  => 'read-vendor',
                ],

                [
                    'text' => 'Customer',
                    'url'  => 'admin/customer',
                    'icon' => 'female',
                    'permission'=>'read-customer',
                ],

                [
                    'text' => 'Kategori Produk',
                    'url'  => 'admin/kategoriproduk',
                    'icon' => 'tasks',
                    'permission'  => 'read-kategori',
                ],
                
                [
                    'text' => 'Unit',
                    'url'  => 'admin/unit',
                    'icon' => 'briefcase',
                    'permission'  => 'read-unit',
                ],

                [
                    'text' => 'Ukuran',
                    'url'  => 'admin/ukuran',
                    'icon' => 'cogs',
                    'permission'  => 'read-ukuran',
                ],

                [
                    'text'        => 'Merek',
                    'url'         => 'admin/merek',
                    'icon'        => 'building',
                    'permission'  => 'read-merek',
                ],

                [
                    'text' => 'Satuan',
                    'url'  => 'admin/satuan',
                    'icon' => 'wrench',
                    'permission'  => 'read-satuan',
                ],

                [
                    'text' => 'Produk',
                    'url'  => 'admin/produk',
                    'icon' => 'briefcase',
                    'permission'  => 'read-produk',
                ],

                [
                    'text' => 'Produk 1',
                    'url'  => 'admin/produk1',
                    'icon' => 'briefcase',
                    'active' => ['admin/produk1','admin/produk1/detail', 'admin/produk1/detail/*'],
                    'permission'  => 'read-produk',
                ],

                [
                    'text' => 'Konversi',
                    'url'  => 'admin/konversi',
                    'icon' => 'exchange',
                    'permission'  => 'read-konversi',
                ],
            ]

        ],

        [
            'text' => 'Transaksi',
            'icon' => 'th-list',
            'permission'  => 'read-transaksi',
            'submenu' => [
                
                [
                    'text' => 'Permintaan Kasbon',
                    'url'  => 'admin/kasbon',
                    'icon' => 'hand-paper-o',
                    'permission'  => 'read-kasbon',
                ],
                
                [
                    'text' => 'NPPB',
                    'url'  => 'admin/memo',
                    'icon' => 'sticky-note',
                    'permission'  => 'read-memo',
                ],

                [
                    'text' => 'Request Pembelian',
                    'url'  => 'admin/requestpembelian',
                    'icon' => 'newspaper-o',
                    'permission'  => 'read-requestbeli',
                ],

                [
                    'text' => 'Pembelian',
                    'url'  => 'admin/pembelian',
                    'icon' => 'cart-arrow-down',
                    'permission'  => 'read-pembelian',
                ],

                [
                    'text' => 'Penerimaan',
                    'url'  => 'admin/penerimaan',
                    'icon' => 'sign-in',
                    'permission'  => 'read-penerimaan',
                ],
                
                [
                    'text' => 'Work Order',
                    'url'  => 'admin/workorder',
                    'icon' => 'cart-arrow-down',
                    'permission'  => 'read-workorder',
                ],

                [
                    'text' => 'Pemakaian',
                    'url'  => 'admin/pemakaian',
                    'icon' => 'sign-out',
                    'permission'  => 'read-pemakaian',
                ],

                [
                    'text' => 'Pemakaian Ban',
                    'url'  => 'admin/pemakaianban',
                    'icon' => 'dot-circle-o',
                    'permission'  => 'read-pemakaianban',
                ],

                // [
                //     'text' => 'Penjualan',
                //     'url'  => 'admin/penjualan',
                //     'icon' => 'money',
                //     'permission'  => 'read-penjualan',
                // ],

                [
                    'text' => 'Transfer Out',
                    'url'  => 'admin/transfer',
                    'icon' => 'minus',
                    'permission'  => 'read-transferout',
                ],

                [
                    'text' => 'Transfer In',
                    'url'  => 'admin/transferin',
                    'icon' => 'plus',
                    'permission'  => 'read-transferin',
                ],

                [
                    'text' => 'Adjusment/Penyesuaian',
                    'url'  => 'admin/adjustment',
                    'icon' => 'check-square-o',
                    'permission'  => 'read-adjustment',
                ],


                [
                    'text' => 'Stok Opname',
                    'url'  => 'admin/opname',
                    'icon' => 'check-square',
                    'permission'  => 'read-opname',
                ],

                [
                    'text' => 'Retur Pembelian',
                    'url'  => 'admin/returpembelian',
                    'icon' => 'mail-reply-all',
                    'permission'  => 'read-returpembelian',
                ],
                
                [
                    'text' => 'Retur Pemakaian',
                    'url'  => 'admin/returpemakaian',
                    'icon' => 'mail-reply-all',
                    'permission'  => 'read-returpembelian',
                ],

                [
                    'text' => 'Disassembling',
                    'url'  => 'admin/disassembling',
                    'icon' => 'random',
                    'permission'  => 'read-disassembling',
                ],

                [
                    'text' => 'Assembling',
                    'url'  => 'admin/assembling',
                    'icon' => 'recycle',
                    'permission'  => 'read-assembling',
                ],

                // [
                //     'text' => 'Retur Pemakaian',
                //     'url'  => 'admin/returpakai',
                //     'icon' => 'history',
                // ],

                [
                    'text' => 'Retur Penjualan',
                    'url'  => 'admin/returjual',
                    'icon' => 'history',
                    'permission'  => 'read-returjual',
                ],

            ]
        ],

        [
            'text' => 'Laporan',
            'icon' => 'folder-open',
            'permission' => 'read-laporan',
            'submenu' => [

                [
                    'text'        => 'Laporan Data Produk',
                    'url'         => 'admin/laporanproduk',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanproduk',
                ],

                [
                    'text'        => 'Laporan Kartu Stok',
                    'url'         => 'admin/kartustok',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-kartustok',
                ],

                [
                    'text'        => 'Laporan Pembelian',
                    'url'         => 'admin/laporanpembelian',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpembelian',
                ],

                [
                    'text'        => 'Laporan Retur Pembelian',
                    'url'         => 'admin/laporanreturpembelian',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanreturpembelian',
                ],
                
                [
                    'text'        => 'Laporan Penerimaan',
                    'url'         => 'admin/laporanpenerimaan',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpenerimaan',
                ],
                
                [
                    'text'        => 'Laporan Pemakaian Per Produk',
                    'url'         => 'admin/laporanpemakaianproduk',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpemakaian',
                ],

                [
                    'text'        => 'Laporan Pemakaian',
                    'url'         => 'admin/laporanpemakaian',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpemakaian',
                ],
                
                [
                    'text'        => 'Report Maintenance Record',
                    'url'         => 'admin/laporanmaintenance',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpemakaian',
                ],

                [
                    'text'        => 'Laporan QTY Pemakaian',
                    'url'         => 'admin/laporanpemakaianqty',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpemakaian',
                ],

                [
                    'text'        => 'Laporan Pemakaian Ban',
                    'url'         => 'admin/laporanpemakaianban',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpemakaianban',
                ],

                [
                    'text'        => 'Laporan Penjualan',
                    'url'         => 'admin/laporanpenjualan',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanpenjualan',
                ],

                [
                    'text'        => 'Laporan Retur Penjualan',
                    'url'         => 'admin/laporanreturpenjualan',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanreturpenjualan',
                ],

                [
                    'text'        => 'Laporan Transfer In',
                    'url'         => 'admin/laporantransferin',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporantransferin',
                ],

                [
                    'text'        => 'Laporan Transfer Out',
                    'url'         => 'admin/laporantransferout',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporantransferout',
                ],

                [
                    'text'        => 'Laporan Adjusment',
                    'url'         => 'admin/laporanadjustment',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanadjustment',
                ],

                [
                    'text'        => 'Laporan Opname',
                    'url'         => 'admin/laporanopname',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanopname',
                ],

                [
                    'text'        => 'Laporan Produk Bulanan',
                    'url'         => 'admin/laporanprodukbulanan',
                    'icon'        => 'bar-chart',
                    'permission'  => 'read-laporanprodukbulanan',
                ],
            ]
        ],

        [
            'text' => 'Utility',
            'icon' => 'calendar-check-o',
            'permission' => 'read-utility',
            'submenu' => [

                [
                    'text'        => 'End Of Month',
                    'url'         => 'admin/endofmonth',
                    'icon'        => 'file-text',
                    'permission'  => 'read-endofmonth',
                ],
                
                [
                    'text'        => 'EOM per Part (Big Data)',
                    'url'         => 'admin/endofmonthpart',
                    'icon'        => 'file-text',
                    'permission'  => 'read-endofmonthpart',
                ],

                [
                    'text'        => 'Re-Open | Re-Open Close',
                    'url'         => 'admin/reopen',
                    'icon'        => 'folder-open-o',
                    'permission'  => 'read-reopen',
                ],
            ]
        ],
        
        
        [
            'text' => 'Re-Calculate',
            'icon' => 'check',
            'permission'  => 'read-recalculate',
            'submenu' => [

                [
                    'text'        => 'Calculate Monthly',
                    'url'         => 'admin/checkmonthly',
                    'icon'        => 'check-circle-o',
                ],
                
                [
                    'text'        => 'Calculate Monthly (Per Part)',
                    'url'         => 'admin/checkmonthlypart',
                    'icon'        => 'check-circle-o',
                ],

                // [
                //     'text'        => 'Calculate Penerimaan',
                //     'url'         => 'admin/checkpenerimaan',
                //     'icon'        => 'check-circle',
                // ],

                // [
                //     'text'        => 'Calculate Pemakaian',
                //     'url'         => 'admin/checkpemakaian',
                //     'icon'        => 'check-square-o',
                // ],

                // [
                //     'text'        => 'Calculate Pemakaian Ban',
                //     'url'         => 'admin/checkpemakaianban',
                //     'icon'        => 'check-square',
                // ],

                // [
                //     'text'        => 'Calculate Penjualan',
                //     'url'         => 'admin/checkpenjualan',
                //     'icon'        => 'check-circle-o',
                // ],

                // [
                //     'text'        => 'ReCalculate Pemakaian',
                //     'url'         => 'admin/recalculatepemakaian',
                //     'icon'        => 'calculator',
                //     'permission'  => 'read-recalculate',
                // ],
            ]
        ],


        'ACCOUNT SETTINGS',
        [
            'text' => 'Users | Roles | Permissions',
            'url'  => 'admin/users',
            'icon' => 'user',
            'permission' => 'read-users'

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Choose what filters you want to include for rendering the menu.
    | You can add your own filters to this array after you've created them.
    | You can comment out the GateFilter if you don't want to use Laravel's
    | built in Gate functionality
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SubmenuFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        // JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        App\Menu\MenuFilter::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Choose which JavaScript plugins should be included. At this moment,
    | only DataTables is supported as a plugin. Set the value to true
    | to include the JavaScript file from a CDN via a script tag.
    |
    */

    'plugins' => [
        'datatables' => true,
        'select2'    => false,
        'chartjs'    => false,
    ],
];
