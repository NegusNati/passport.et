<?php if (isset($component)) { $__componentOriginal74daf2d0a9c625ad90327a6043d15980 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal74daf2d0a9c625ad90327a6043d15980 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="md:flex md:items-center md:justify-between md:gap-2">
        <div>
            <div class="inline-block rounded-full bg-red-500/20 px-3 py-2 dark:bg-red-500/20">
                <span class="hidden text-sm font-bold leading-5 text-red-500 md:inline-block lg:text-base">
                    <?php echo e($exception->class()); ?>

                </span>
                <span class="text-sm font-bold leading-5 text-red-500 md:hidden lg:text-base">
                    <?php echo e(implode(' ', array_slice(explode('\\', $exception->class()), -1))); ?>

                </span>
            </div>
            <div class="mt-4 text-lg font-semibold text-gray-900 dark:text-white lg:text-2xl"><?php echo e($exception->message()); ?></div>
        </div>

        <div class="hidden text-right md:block md:min-w-64">
            <div>
                <span class="rounded-full bg-gray-200 px-3 py-2 text-sm leading-5 text-gray-900 dark:bg-gray-800 dark:text-white">
                    <?php echo e($exception->request()->method()); ?> <?php echo e($exception->request()->httpHost()); ?>

                </span>
            </div>
            <div class="mt-4 px-4">
                <span class="text-sm text-gray-500 dark:text-gray-400">PHP <?php echo e(PHP_VERSION); ?> — Laravel <?php echo e(app()->version()); ?></span>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal74daf2d0a9c625ad90327a6043d15980)): ?>
<?php $attributes = $__attributesOriginal74daf2d0a9c625ad90327a6043d15980; ?>
<?php unset($__attributesOriginal74daf2d0a9c625ad90327a6043d15980); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal74daf2d0a9c625ad90327a6043d15980)): ?>
<?php $component = $__componentOriginal74daf2d0a9c625ad90327a6043d15980; ?>
<?php unset($__componentOriginal74daf2d0a9c625ad90327a6043d15980); ?>
<?php endif; ?>
<?php /**PATH /var/www/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/header.blade.php ENDPATH**/ ?>