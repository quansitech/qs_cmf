'use strict';

(function () {
    require('modules/ignore/jquery-1.8.3/jquery');

    function main() {
        utils().bannerH();
        EventHanlder();
    }

    function utils() {
        return {
            bannerH: function bannerH() {
                $('.banner,.banner .bd li,.banner .bd ul').height($(window).height());
            },
            newsFocus: function newsFocus(page, hd, bd) {
                hd.eq(page).addClass('on').siblings('li').removeClass('on');
                bd.eq(page).css('z-index', '1').stop().animate({
                    'opacity': '1'
                }).siblings('li').css('z-index', '0').stop().animate({
                    'opacity': '0'
                });
            }
        };
    }

    //事件
    function EventHanlder() {
        //返回顶部
        $('.item4').on('click', function () {
            $('body,html').stop().animate({
                scrollTop: 0
            });
        });
        //信息发布切换
        $('#xz-2-item-a-r-list li').css('opacity', '0').eq(0).css('opacity', '1');
        $('.xz-2-item-a-r-nav span').on('mouseenter', function () {
            $('#xz-2-item-a-r-list li').eq($(this).index()).css('z-index', '1').animate({
                'opacity': '1'
            }).siblings('li').css('z-index', '0').animate({
                'opacity': '0'
            });
            $(this).addClass('on').siblings('span').removeClass('on');
        });

        // 常见问题
        $('.common-problem dt').on('click', function () {
            var _this = this;
            var _dt = $('.common-problem dt');
            _dt.each(function (index) {
                if (_dt.get(index) == _this) {
                    _dt.eq(index).addClass('on').next('dd').slideDown(300);
                } else {
                    _dt.eq(index).removeClass('on').next('dd').slideUp(300);
                }
            });
        });
        $('dd .hide_btn').on('click', function () {
            $(this).parent().slideUp().prev('dt').removeClass('on');
        });

        // 活动图集
        $('body').on('click', function () {
            $('.atlas-data').fadeOut();
        });
        $('.activity-atlas img').on('click', function (e) {
            e.stopPropagation();
            $('.atlas-data').fadeIn().children('img').attr('src', $(this).attr('data-img'));
        });
        // $('.atlas-close').on('click', function(e){
        //     e.stopPropagation()
        //     $('.atlas-data').fadeOut();
        // });

        // function newsFocus(page){

        // }
        //
        // 新闻列表切换
        var newsFocus_hd = $('.news-focus .hd li');
        var hd_size = newsFocus_hd.length() - 1;
        var newsFocus_bd = $('.news-focus .bd li');
        var index = 0;
        newsFocus_hd.on('mouseenter', function () {
            index = $(this).index();
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });
        $('.news-next').on('click', function () {
            if (index >= hd_size) {
                index = -1;
            }
            index++;
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });
        $('.news-prev').on('click', function () {
            if (index <= 0) {
                index = 3;
                console.log(index);
            }
            index--;
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });

        // 创建队伍筹款目标
        $('.form-span-radio span').on('click', function () {
            $(this).addClass('on').siblings('span').removeClass('on');;
        });
        $('.form-span-radio .input-text').on('focus', function () {
            $('.form-span-radio span').removeClass('on');
        });

        // 队伍创建成功
        $('.pop-success-close').on('click', function () {
            $('.pop-success').fadeOut(300);
        });

        // 系统消息展开与收起
        $('.system-message-item').find('a').on('click', function () {
            $(this).nextAll('.system-message-con').fadeIn();
        });
        $('.system-message-close').on('click', function () {
            $(this).parent('.system-message-con').hide();
        });

        /*个人资料展开与收起*/
        $('.public-page-l-1>dd').on('click', function () {
            var _this = this;
            $(this).children('ul').slideDown(200);
            $(this).siblings('dd').children('ul').slideUp(300);
        });
    }
    $(function () {
        main();
    });
})();