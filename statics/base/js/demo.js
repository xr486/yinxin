/**

 * Created by zhuhe on 2017/12/9.

 */

/**

 *

 */
$(window).resize(function() {
 var a= $(window).width();
 $("#right").css('width',a-250+'px');
});
$(function(){
    
     var a= $(window).width();
     $("#right").css('width',a-250+'px');
    

    $(".uitem").hide(); //页面加载完成后隐藏2级菜单

    $(".titem").hide();

    //交替执行点击事件

    $(".litem>a").toggle(

        // function Down(){

        //     $(this).next().slideDown();

        //     var index=$(".uitem").index($(this).next());

        //     $(".uitem").not(":eq("+index+")").slideUp();

        // }
        function(){
            $(this).next().slideDown();
        }, 
        function(){
            $(this).next().slideUp();
            // $('.litem uitem li').find("ul").trigger("click");
            // $('.uitem li').find("ul").slideUp();
        } 
    );
    $('.uitem li').toggle( 
        function(e){
            $(this).find("ul").slideDown();
        }, 

        function(){
            $(this).find("ul").slideUp();
        } 

        ); 
$('.uitem li ul li>a').click(function(e){
            var o = e.target;
            if (o.tagName == 'A') window.open(o.href,"right");
});

    // $(".uitem li").hover(

    //     function () {

    //         $(this).find("ul").show();

    //     },

    //     function () {

    //         $(this).find("ul").hide();

    //     }



    // );

});

