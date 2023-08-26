<?php $__env->startSection('page-title'); ?>
    <?php echo e($lead->name); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/dropzone.min.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/dropzone-amd-module.min.js')); ?>"></script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#lead-sidenav',
            offset: 300
        })
        Dropzone.autoDiscover = false;
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            maxFilesize: 20,
            parallelUploads: 1,
            filename: false,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "<?php echo e(route('leads.file.upload',$lead->id)); ?>",
            success: function (file, response) {
                if (response.is_success) {
                    dropzoneBtn(file, response);
                } else {
                    myDropzone.removeFile(file);
                    show_toastr('Error', response.error, 'error');
                }
            },
            error: function (file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    show_toastr('Error', response.error, 'error');
                } else {
                    show_toastr('Error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function (file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("lead_id", <?php echo e($lead->id); ?>);
        });

        function dropzoneBtn(file, response) {
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "badge bg-info mx-1");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('data-original-title', "<?php echo e(__('Download')); ?>");
            download.innerHTML = "<i class='ti ti-download'></i>";

            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "badge bg-danger mx-1");
            del.setAttribute('data-toggle', "tooltip");
            del.setAttribute('data-original-title', "<?php echo e(__('Delete')); ?>");
            del.innerHTML = "<i class='ti ti-trash'></i>";

            del.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm("Are you sure ?")) {
                    var btn = $(this);
                    $.ajax({
                        url: btn.attr('href'),
                        data: {_token: $('meta[name="csrf-token"]').attr('content')},
                        type: 'DELETE',
                        success: function (response) {
                            if (response.is_success) {
                                btn.closest('.dz-image-preview').remove();
                            } else {
                                show_toastr('Error', response.error, 'error');
                            }
                        },
                        error: function (response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                show_toastr('Error', response.error, 'error');
                            } else {
                                show_toastr('Error', response, 'error');
                            }
                        }
                    })
                }
            });

            var html = document.createElement('div');
            html.appendChild(download);
            <?php if(Auth::user()->type != 'client'): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
            html.appendChild(del);
            <?php endif; ?>
            <?php endif; ?>

            file.previewTemplate.appendChild(html);
        }

        <?php $__currentLoopData = $lead->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(file_exists(storage_path('lead_files/'.$file->file_path))): ?>
        // Create the mock file:
        var mockFile = {name: "<?php echo e($file->file_name); ?>", size: <?php echo e(\File::size(storage_path('lead_files/'.$file->file_path))); ?>};
        // Call the default addedfile event handler
        myDropzone.emit("addedfile", mockFile);
        // And optionally show the thumbnail of the file:
        myDropzone.emit("thumbnail", mockFile, "<?php echo e(asset(Storage::url('lead_files/'.$file->file_path))); ?>");
        myDropzone.emit("complete", mockFile);

        dropzoneBtn(mockFile, {download: "<?php echo e(route('leads.file.download',[$lead->id,$file->id])); ?>", delete: "<?php echo e(route('leads.file.delete',[$lead->id,$file->id])); ?>"});
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
        $('.summernote-simple').on('summernote.blur', function () {

            $.ajax({
                url: "<?php echo e(route('leads.note.store',$lead->id)); ?>",
                data: {_token: $('meta[name="csrf-token"]').attr('content'), notes: $(this).val()},
                type: 'POST',
                success: function (response) {
                    if (response.is_success) {
                        // show_toastr('Success', response.success,'success');
                    } else {
                        show_toastr('Error', response.error, 'error');
                    }
                },
                error: function (response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('Error', response.error, 'error');
                    } else {
                        show_toastr('Error', response, 'error');
                    }
                }
            })
        });
        <?php else: ?>
        $('.summernote-simple').summernote('disable');
        <?php endif; ?>

    </script>
    <script>
    function printme(){
        window.print();
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('leads.index')); ?>"><?php echo e(__('Lead')); ?></a></li>
    <li class="breadcrumb-item"> <?php echo e($lead->name); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('action-btn'); ?>
    <div class="float-end">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('convert lead to deal')): ?>
            <?php if(!empty($deal)): ?>
                <a href="<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('View Deal')): ?> <?php if($deal->is_active): ?> <?php echo e(route('deals.show',$deal->id)); ?> <?php else: ?> # <?php endif; ?> <?php else: ?> # <?php endif; ?>" data-size="lg" data-bs-toggle="tooltip" title=" <?php echo e(__('Already Converted To Deal')); ?>" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>
            <?php else: ?>




                <a href="#" data-size="lg" data-url="<?php echo e(URL::to('leads/'.$lead->id.'/show_convert')); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Convert ['.$lead->subject.'] To Deal')); ?>" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>


            <?php endif; ?>
        <?php endif; ?>
        <a href="#" data-url="<?php echo e(URL::to('leads/'.$lead->id.'/labels')); ?>" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="<?php echo e(__('Label')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-bookmark"></i>
        </a>
            <a href="#" onclick="printme();"  title="<?php echo e(__('Print Data')); ?>" class="btn btn-sm btn-primary btnprn">
  <i class="fa fa-print"></i>
        </a>
        <a href="#" data-size="lg" data-url="<?php echo e(route('leads.edit',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('sweetalert::alert', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="lead-sidenav">
                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#general" class="list-group-item list-group-item-action border-0"><?php echo e(__('General')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>

                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#users_products" class="list-group-item list-group-item-action border-0"><?php echo e(__('Users').' | '.__('Products')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>

                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#sources_emails" class="list-group-item list-group-item-action border-0"><?php echo e(__('Sources').' | '.__('Emails')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>
                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#discussion_note" class="list-group-item list-group-item-action border-0"><?php echo e(__('Discussion').' | '.__('Notes')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>
                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#files" class="list-group-item list-group-item-action border-0"><?php echo e(__('Files')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>
                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#calls" class="list-group-item list-group-item-action border-0"><?php echo e(__('Calls')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>
                            <?php if(Auth::user()->type != 'client'): ?>
                                <a href="#activity" class="list-group-item list-group-item-action border-0"><?php echo e(__('Activity')); ?>

                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <?php
                    $products = $lead->products();
                    $sources = $lead->sources();
                    $calls = $lead->calls;
                       $reminds = $lead->reminds;
                    $emails = $lead->emails;
                    ?>
                    <div id="general" class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-success">
                                            <i class="ti ti-test-pipe"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0"><?php echo e(__('Pipeline')); ?></p>
                                            <h5 class="mb-0 text-success"><?php echo e($lead->pipeline->name); ?></h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 my-3 my-sm-0">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-server"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0"><?php echo e(__('Stage')); ?></p>
                                            <h5 class="mb-0 text-primary"><?php echo e($lead->stage->name); ?></h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-calendar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0"><?php echo e(__('Created')); ?></p>
                                            <h5 class="mb-0 text-warning"><?php echo e(\Auth::user()->dateFormat($lead->created_at)); ?></h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-chart-bar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <h3 class="mb-0 text-info"><?php echo e($precentage); ?>%</h3>
                                            <div class="progress mb-0">
                                                <div class="progress-bar bg-info" style="width: <?php echo e($precentage); ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="card">
                            <div class="card-body">
                                <div class="row">
                                <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th><?php echo e(__('Name')); ?></th>
                                                    <th><?php echo e(__('Phone')); ?></th>
                                                    <th><?php echo e(__('Street#')); ?></th>
                                                    <th><?php echo e(__('House#')); ?></th>
                                                    <th><?php echo e(__('Area')); ?></th>
                                                    <th><?php echo e(__('Sector')); ?></th>
                                                    <th><?php echo e(__('HouseSize')); ?></th>
                                                    <th><?php echo e(__('Who is he')); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                               
                                                    <tr>
                                                        <td>
                                                          <?php echo e($lead->name); ?>

                                                        </td>
                                                        <td>
                                                        <?php echo e($lead->phone); ?>  
                                                        </td>
                                                        <td>
                                                            &nbsp;<?php echo e($lead->streetno); ?>&#160;
                                                        </td> 
                                                        <td>
                                                            <?php echo e($lead->houseno); ?>

                                                        </td>
                                                      <td>
                                                          <?php echo e($lead->area); ?>

                                                     </td>
                                                     <td>
                                                          <?php echo e($lead->sector); ?>

                                                     </td>
                                                       <td>
                                                        <?php echo e($lead->housesize); ?>

                                                       </td>
                                                       <td>
                                                        <?php echo e($lead->whoishe); ?>

                                                       </td>
                                                    </tr>
                                                
                                                </tbody>
                                            </table>
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted"><?php echo e(__('Product')); ?></small>
                                            <h3 class="m-0"><?php echo e(count($products)); ?></h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-shopping-cart"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted"><?php echo e(__('Source')); ?></small>
                                            <h3 class="m-0"><?php echo e(count($sources)); ?></h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-social"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted"><?php echo e(__('Files')); ?></small>
                                            <h3 class="m-0"><?php echo e(count($lead->files)); ?></h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-file"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="users_products">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="float-end">
                                            <a href="#" data-size="md" data-url="<?php echo e(route('leads.users.edit',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add User')); ?>" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5><?php echo e(__('Users')); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th><?php echo e(__('Name')); ?></th>
                                                    <th><?php echo e(__('Action')); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $__currentLoopData = $lead->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <img <?php if($user->avatar): ?> src="<?php echo e(asset('/storage/uploads/avatar/'.$user->avatar)); ?>" <?php else: ?> src="<?php echo e(asset('/storage/uploads/avatar/avatar.png')); ?>" <?php endif; ?> class="wid-30 rounded-circle me-3" alt="avatar image">
                                                                </div>
                                                                <p class="mb-0"><?php echo e($user->name); ?></p>
                                                            </div>
                                                        </td>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['leads.users.destroy', $lead->id,$user->id],'id'=>'delete-form-'.$lead->id]); ?>

                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                                    <?php echo Form::close(); ?>

                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="float-end">
                                            <a href="#" data-size="md" data-url="<?php echo e(route('leads.products.edit',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add Product')); ?>" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5><?php echo e(__('Products')); ?></h5>

                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th><?php echo e(__('Name')); ?></th>
                                                    <th><?php echo e(__('Price')); ?></th>
                                                    <th><?php echo e(__('Action')); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $__currentLoopData = $lead->products(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo e($product->name); ?>

                                                        </td>
                                                        <td>
                                                            <?php echo e(\Auth::user()->priceFormat($product->sale_price)); ?>

                                                        </td>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['leads.products.destroy', $lead->id,$product->id]]); ?>

                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                                    <?php echo Form::close(); ?>

                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="sources_emails">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="float-end">
                                            <a href="#" data-size="md" data-url="<?php echo e(route('leads.sources.edit',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add Source')); ?>" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5><?php echo e(__('Sources')); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th><?php echo e(__('Name')); ?></th>
                                                    <th><?php echo e(__('Action')); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($source->name); ?> </td>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead')): ?>
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['leads.sources.destroy', $lead->id,$source->id],'id'=>'delete-form-'.$lead->id]); ?>

                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                                    <?php echo Form::close(); ?>

                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create lead email')): ?>
                                            <div class="float-end">
                                                <a href="#" data-size="md" data-url="<?php echo e(route('leads.emails.create',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Create Email')); ?>" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <h5><?php echo e(__('Emails')); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mt-2">
                                            <?php if(!$emails->isEmpty()): ?>
                                                <?php $__currentLoopData = $emails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $email): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="<?php echo e(asset('/storage/uploads/avatar/avatar.png')); ?>"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0"><?php echo e($email->subject); ?></h6>
                                                                        <span class="text-muted text-sm"><?php echo e($email->to); ?></span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        <?php echo e($email->created_at->diffForHumans()); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <li class="text-center">
                                                    <?php echo e(__(' No Emails Available.!')); ?>

                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="discussion_note">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="float-end">
                                            <a href="#" data-size="lg" data-url="<?php echo e(route('leads.discussions.create',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add Message')); ?>" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5><?php echo e(__('Discussion')); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mt-2">
                                            <?php if(!$lead->discussions->isEmpty()): ?>
                                                <?php $__currentLoopData = $lead->discussions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discussion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="<?php if($discussion->user->avatar): ?> <?php echo e(asset('/storage/uploads/avatar/'.$discussion->user->avatar)); ?> <?php else: ?> <?php echo e(asset('/storage/uploads/avatar/avatar.png')); ?> <?php endif; ?>"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0"> <?php echo e($discussion->comment); ?></h6>
                                                                        <span class="text-muted text-sm"><?php echo e($discussion->user->name); ?></span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        <?php echo e($discussion->created_at->diffForHumans()); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <li class="text-center">
                                                    <?php echo e(__(' No Data Available.!')); ?>

                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><?php echo e(__('Notes')); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="summernote-simple"><?php echo $lead->notes; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="files" class="card">
                        <div class="card-header ">
                            <h5><?php echo e(__('Files')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                        </div>
                    </div>
                    <div id="calls" class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <a href="#" data-size="lg" data-url="<?php echo e(route('leads.calls.create',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add Call')); ?>" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                            <h5><?php echo e(__('Calls')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th width=""><?php echo e(__('Subject')); ?></th>
                                        <th><?php echo e(__('Call Type')); ?></th>
                                        <th><?php echo e(__('Duration')); ?></th>
                                        <th><?php echo e(__('User')); ?></th>
                                        <th><?php echo e(__('Action')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $calls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $call): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($call->subject); ?></td>
                                            <td><?php echo e(ucfirst($call->call_type)); ?></td>
                                            <td><?php echo e($call->duration); ?></td>
                                            <td><?php echo e(isset($call->getLeadCallUser) ? $call->getLeadCallUser->name : '-'); ?></td>
                                            <td>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit lead call')): ?>
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="<?php echo e(URL::to('leads/'.$lead->id.'/call/'.$call->id.'/edit')); ?>" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-title="<?php echo e(__('Role Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete lead call')): ?>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['leads.calls.destroy', $lead->id,$call->id],'id'=>'delete-form-'.$lead->id]); ?>

                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="calls" class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <a href="#" data-size="lg" data-url="<?php echo e(route('leads.remind.create',$lead->id)); ?>" data-ajax-popup="true" data-bs-toggle="tooltip" title="<?php echo e(__('Add Reminder')); ?>" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                            <h5><?php echo e(__('Reminders')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th width=""><?php echo e(__('Name')); ?></th>
                                        <th><?php echo e(__('Description')); ?></th>
                                        <th><?php echo e(__('Date')); ?></th>
                                        <th><?php echo e(__('Time')); ?></th>
                                        <th><?php echo e(__('Action')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__currentLoopData = $reminds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $remind): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($remind->name); ?></td>
                                            <td><?php echo e($remind->description); ?></td>
                                            <td><?php echo e($remind->time); ?></td>
                                            <td><?php echo e($remind->date); ?></td>
                                            <td>
                                                 
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="<?php echo e(URL::to('leads/'.$lead->id.'/remind/'.$remind->id.'/edit')); ?>" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>" data-title="<?php echo e(__('Role Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                               <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open(['method' => 'DELETE', 'route' => ['leads.reminds.destroy', $lead->id,$remind->id],'id'=>'delete-form-'.$lead->id]); ?>

                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>"><i class="ti ti-trash text-white"></i></a>

                                                        <?php echo Form::close(); ?>

                                                    </div>
                                            
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="activity" class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Activity')); ?></h5>
                        </div>
                        <div class="card-body height-450">

                            <div class="row" style="height:450px !important;overflow-y: scroll;">
                                <ul class="event-cards list-group list-group-flush mt-3 w-100">
                                    <?php if(!$lead->activities->isEmpty()): ?>
                                        <?php $__currentLoopData = $lead->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="list-group-item card mb-3">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avtar bg-primary">
                                                                <i class="fas <?php echo e($activity->logIcon()); ?>"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <span class="text-dark text-sm"><?php echo e(__($activity->log_type)); ?></span>
                                                                <h6 class="m-0"><?php echo $activity->getLeadRemark(); ?></h6>
                                                                <small class="text-muted"><?php echo e($activity->created_at->diffForHumans()); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">

                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        No activity found yet.
                                    <?php endif; ?>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home2/babarras/public_html/resources/views/leads/show.blade.php ENDPATH**/ ?>