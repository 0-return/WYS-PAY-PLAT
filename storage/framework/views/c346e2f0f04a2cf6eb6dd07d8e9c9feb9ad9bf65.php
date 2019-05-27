<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <?php echo $__env->yieldContent('meta'); ?>
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <meta name="keywords" content="<?php echo $__env->yieldContent('keywords'); ?>">
    <meta name="description" content="<?php echo $__env->yieldContent('description'); ?>">
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <?php echo $__env->yieldContent('css'); ?>
    <script src=<?php echo e(asset('/js/jquery.min.js?v=2.1.4')); ?>></script>
    <link href="<?php echo e(asset('antui/dist/antui.min.css')); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://as.alipayobjects.com/g/component/swiper/3.2.7/swiper.min.css" />
</head>
<body class="fixed-sidebar full-height-layout gray-bg">
<?php echo $__env->yieldContent('content'); ?>
<!-- 全局js -->

<?php echo $__env->yieldContent('js'); ?>
</body>

</html>
