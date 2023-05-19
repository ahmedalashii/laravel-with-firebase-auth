

<?php $__env->startSection('MainContent'); ?>
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4 mb-4">Trainees</h1>
            <section class="mt-3 mb-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-user me-1"></i>
                        Trainee Information
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Email address</th>
                                    <th>Address</th>
                                    <th>Gender</th>
                                    <th>CV</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($trainees->isEmpty()): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No trainees found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $__currentLoopData = $trainees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trainee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo e($trainee->avatar); ?>" class="rounded-circle me-1" width="37px" height="40px"
                                                    alt="<?php echo e($trainee->displayName); ?>'s avatar" />
                                                <?php echo e($trainee->displayName); ?>

                                            </td>
                                            <td><?php echo e($trainee->email); ?></td>
                                            <td><?php echo e($trainee->address); ?></td>
                                            <td><?php echo e(Str::ucfirst($trainee->gender)); ?></td>
                                            <td><a href="<?php echo e($trainee->cv); ?>">Download
                                                    CV</a></td>
                                            <td><a href="<?php echo e(route('manager.trainees-edit', $trainee->id)); ?>"
                                                    class="btn btn-success">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-5">
                            <?php echo e($trainees->links()); ?>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.managerLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/vagrant/laravel/tms-laravel/resources/views/manager/trainees.blade.php ENDPATH**/ ?>