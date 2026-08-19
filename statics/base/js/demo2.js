$(function () {
    // 窗口尺寸变化时调整右侧区域宽度
    $(window).resize(function () {
        var windowWidth = $(window).width();
        $("#right").css('width', windowWidth - 150 + 'px');
    });

    // 页面加载完成后执行的操作
    var windowWidth = $(window).width();
    $("#right").css('width', windowWidth - 150 + 'px');
    $(".uitem").hide(); // 隐藏二级菜单
    $(".titem").hide(); // 隐藏三级菜单

    // 一级菜单的点击事件
    $(".litem > a").on('click', function () {
        var isExpanded = $(this).next(".uitem").is(":visible"); // 判断当前菜单的二级菜单是否可见
        $(".uitem").slideUp(500); // 收起所有二级菜单
        $(".titem").hide(); // 收起所有三级菜单
        if (!isExpanded) {
            // 如果当前的二级菜单不可见，则展开它
            $(this).next(".uitem").slideDown(500);
        }
    });
    // 二级菜单的点击事件(对相同菜单始终保持打开状态)
    // $('.uitem li').on('click', function (e) {
    //     e.stopPropagation(); // 阻止事件冒泡到父元素
    //     // 隐藏除了当前点击的菜单项之外的所有三级菜单
    //     $(".uitem li").not(this).find(".titem").slideUp(0);
    //     // 切换当前点击的菜单项的三级菜单的显示状态
    //     $(this).find(".titem").slideDown(0);
    //     // 移除其他二级菜单项的 active-menu 类
    //     $('.uitem li').removeClass('active-menu');
    //     // 给当前点击的二级菜单项添加 active-menu 类
    //     $(this).addClass('active-menu');
    // });
    // 二级菜单的点击事件(点击切换收起还是展开)
    // $('.uitem li').on('click', function (e) {
    //     e.stopPropagation(); // 阻止事件冒泡到父元素
    //     // 隐藏除了当前点击的菜单项之外的所有三级菜单
    //     $(".uitem li").not(this).find(".titem").slideUp(0);
    //     // 切换当前点击的菜单项的三级菜单的显示状态
    //     $(this).find(".titem").slideToggle(0);
    //     // 移除其他二级菜单项的 active-menu 类
    //     $('.uitem li').removeClass('active-menu');
    //     // 给当前点击的二级菜单项添加 active-menu 类
    //     $(this).addClass('active-menu');
    // });
    // 二级菜单的悬停事件
    $('.uitem li').hover(
        function () {
            // 鼠标悬停时的操作
            $(this).addClass('active-menu'); // 添加悬停样式
            $(this).find(".titem").slideDown(0); // 显示对应的三级菜单,平滑效果为0s
        },
        function () {
            // 鼠标移开时的操作
            $(this).removeClass('active-menu'); // 移除悬停样式
            $(this).find(".titem").slideUp(0); // 隐藏对应的三级菜单,平滑效果为0.5s
        }
    );
    // 三级菜单的悬停事件
    $('.titem li').hover(
        function () {
            // 鼠标悬停时的操作
            $(this).addClass('active-sub-menu'); // 添加悬停样式
        },
        function () {
            // 鼠标移开时的操作
            $(this).removeClass('active-sub-menu'); // 移除悬停样式
        }
    );
    // 点击页面其他地方关闭所有三级菜单,并清除二级菜单的选中状态
    // $(document).on('click', function () {
    //     $(".titem").slideUp(0);//平滑效果为0.5s
    //     $('.uitem li').removeClass('active-menu');
    // });

    // 阻止三级菜单上的点击事件冒泡
    // $('.titem').on('click', function (e) {
    //     e.stopPropagation();
    // });
});