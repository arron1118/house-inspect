<?php

return [
    'admin' => [
        "homeInfo" => [
            "title" => "项目管理",
            "href" => (string) url('/area/index')
        ],
        "logoInfo" => [
            "title" => "",
            "image" => "/static/images/logo.png",
            "href" => ""
        ],
        "menuInfo" => [
            [
                "title" => "常规管理",
                "icon" => "fa fa-address-book",
                "href" => "",
                "target" => "_self",
                "child" => [
                    [
                        "title" => "项目管理",
                        "href" => (string) url('/area/index'),
                        "icon" => "fa fa-map-location-dot",
                        "target" => "_self"
                    ],
                    [
                        "title" => "房屋管理",
                        "href" => (string) url('/house/index'),
                        "icon" => "fa fa-house",
                        "target" => "_self"
                    ],
                    [
                        "title" => "用户管理",
                        "href" => (string) url('/user/index'),
                        "icon" => "fa-solid fa-users",
                        "target" => "_self"
                    ],
                    [
                        "title" => "系统设置",
                        "href" => (string) url('/config/index'),
                        "icon" => "fa fa-gear",
                        "target" => "_self"
                    ]
                ]
            ]
        ]
    ]

];
