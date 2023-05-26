<?php $__env->startSection('MainContent'); ?>
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">We're excited to have you on board, <span class="text-success"><?php echo e(Auth::guard('advisor')->user()->displayName); ?> 😎</span></h1>

                <section class="mt-3 mb-3">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-clock me-1"></i>
                            Upcoming Events
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0">Data Structures Training Session</h5>
                                        <small>May 17th</small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-success">View Details</a>
                                </li>
                                <li class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0">Meeting with Advisor</h5>
                                        <small>May 20th</small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-success">View Details</a>
                                </li>
                                <li class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Algorithms Midterm Exam</h5>
                                        <small>May 25th</small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-success">View Details</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Training Attendance</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="./attendance.html">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Request a Meeting</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="./request-meeting.html">View
                                    Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Personal File Upload</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="./upload.html">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.advisorLayout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/vagrant/laravel/tms-laravel/resources/views/advisor/index.blade.php ENDPATH**/ ?>