@extends('layouts.admin')
@section('page-title')
    {{$lead->name}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
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
            url: "{{route('leads.file.upload',$lead->id)}}",
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
            formData.append("lead_id", {{$lead->id}});
        });

        function dropzoneBtn(file, response) {
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "badge bg-info mx-1");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('data-original-title', "{{__('Download')}}");
            download.innerHTML = "<i class='ti ti-download'></i>";

            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "badge bg-danger mx-1");
            del.setAttribute('data-toggle', "tooltip");
            del.setAttribute('data-original-title', "{{__('Delete')}}");
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
            @if(Auth::user()->type != 'client')
            @can('edit lead')
            html.appendChild(del);
            @endcan
            @endif

            file.previewTemplate.appendChild(html);
        }

        @foreach($lead->files as $file)
        @if (file_exists(storage_path('lead_files/'.$file->file_path)))
        // Create the mock file:
        var mockFile = {name: "{{$file->file_name}}", size: {{\File::size(storage_path('lead_files/'.$file->file_path))}}};
        // Call the default addedfile event handler
        myDropzone.emit("addedfile", mockFile);
        // And optionally show the thumbnail of the file:
        myDropzone.emit("thumbnail", mockFile, "{{asset(Storage::url('lead_files/'.$file->file_path))}}");
        myDropzone.emit("complete", mockFile);

        dropzoneBtn(mockFile, {download: "{{route('leads.file.download',[$lead->id,$file->id])}}", delete: "{{route('leads.file.delete',[$lead->id,$file->id])}}"});
        @endif
        @endforeach

        @can('edit lead')
        $('.summernote-simple').on('summernote.blur', function () {

            $.ajax({
                url: "{{route('leads.note.store',$lead->id)}}",
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
        @else
        $('.summernote-simple').summernote('disable');
        @endcan

    </script>
    <script>
    function printme(){
        window.print();
    }
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('leads.index')}}">{{__('Lead')}}</a></li>
    <li class="breadcrumb-item"> {{$lead->name}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('convert lead to deal')
            @if(!empty($deal))
                <a href="@can('View Deal') @if($deal->is_active) {{route('deals.show',$deal->id)}} @else # @endif @else # @endcan" data-size="lg" data-bs-toggle="tooltip" title=" {{__('Already Converted To Deal')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>
            @else
{{--                <a href="#" data-size="lg" data-url="{{ URL::to('leads/'.$lead->id.'/show_convert') }}" data-bs-toggle="tooltip" title="{{__('Convert ['.$lead->subject.'] To Deal')}}" class="btn btn-sm btn-primary">--}}
{{--                    <i class="ti ti-exchange"></i>--}}
{{--                </a>--}}

                <a href="#" data-size="lg" data-url="{{ URL::to('leads/'.$lead->id.'/show_convert') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Convert ['.$lead->subject.'] To Deal')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>


            @endif
        @endcan
        <a href="#" data-url="{{ URL::to('leads/'.$lead->id.'/labels') }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('Label')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-bookmark"></i>
        </a>
            <a href="#" onclick="printme();"  title="{{__('Print Data')}}" class="btn btn-sm btn-primary btnprn">
  <i class="fa fa-print"></i>
        </a>
        <a href="#" data-size="lg" data-url="{{ route('leads.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
@endsection

@section('content')

@include('sweetalert::alert')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="lead-sidenav">
                            @if(Auth::user()->type != 'client')
                                <a href="#general" class="list-group-item list-group-item-action border-0">{{__('General')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if(Auth::user()->type != 'client')
                                <a href="#users_products" class="list-group-item list-group-item-action border-0">{{__('Users').' | '.__('Products')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

                            @if(Auth::user()->type != 'client')
                                <a href="#sources_emails" class="list-group-item list-group-item-action border-0">{{__('Sources').' | '.__('Emails')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if(Auth::user()->type != 'client')
                                <a href="#discussion_note" class="list-group-item list-group-item-action border-0">{{__('Discussion').' | '.__('Notes')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if(Auth::user()->type != 'client')
                                <a href="#files" class="list-group-item list-group-item-action border-0">{{__('Files')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if(Auth::user()->type != 'client')
                                <a href="#calls" class="list-group-item list-group-item-action border-0">{{__('Calls')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif
                            @if(Auth::user()->type != 'client')
                                <a href="#activity" class="list-group-item list-group-item-action border-0">{{__('Activity')}}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                </a>
                            @endif

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
                                            <p class="text-muted text-sm mb-0">{{__('Pipeline')}}</p>
                                            <h5 class="mb-0 text-success">{{$lead->pipeline->name}}</h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 my-3 my-sm-0">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-primary">
                                            <i class="ti ti-server"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Stage')}}</p>
                                            <h5 class="mb-0 text-primary">{{$lead->stage->name}}</h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-warning">
                                            <i class="ti ti-calendar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Created')}}</p>
                                            <h5 class="mb-0 text-warning">{{\Auth::user()->dateFormat($lead->created_at)}}</h5>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avtar bg-info">
                                            <i class="ti ti-chart-bar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <h3 class="mb-0 text-info">{{$precentage}}%</h3>
                                            <div class="progress mb-0">
                                                <div class="progress-bar bg-info" style="width: {{$precentage}}%;"></div>
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
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Phone')}}</th>
                                                    <th>{{__('Street#')}}</th>
                                                    <th>{{__('House#')}}</th>
                                                    <th>{{__('Area')}}</th>
                                                    <th>{{__('Sector')}}</th>
                                                    <th>{{__('HouseSize')}}</th>
                                                    <th>{{__('Who is he')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                               
                                                    <tr>
                                                        <td>
                                                          {{$lead->name}}
                                                        </td>
                                                        <td>
                                                        {{$lead->phone}}  
                                                        </td>
                                                        <td>
                                                            &nbsp;{{$lead->streetno}}&#160;
                                                        </td> 
                                                        <td>
                                                            {{$lead->houseno}}
                                                        </td>
                                                      <td>
                                                          {{$lead->area}}
                                                     </td>
                                                     <td>
                                                          {{$lead->sector}}
                                                     </td>
                                                       <td>
                                                        {{$lead->housesize}}
                                                       </td>
                                                       <td>
                                                        {{$lead->whoishe}}
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
                                            <small class="text-muted">{{__('Product')}}</small>
                                            <h3 class="m-0">{{count($products)}}</h3>
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
                                            <small class="text-muted">{{__('Source')}}</small>
                                            <h3 class="m-0">{{count($sources)}}</h3>
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
                                            <small class="text-muted">{{__('Files')}}</small>
                                            <h3 class="m-0">{{count($lead->files)}}</h3>
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
                                            <a href="#" data-size="md" data-url="{{ route('leads.users.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add User')}}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5>{{__('Users')}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($lead->users as $user)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="wid-30 rounded-circle me-3" alt="avatar image">
                                                                </div>
                                                                <p class="mb-0">{{$user->name}}</p>
                                                            </div>
                                                        </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leads.users.destroy', $lead->id,$user->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Form::close() !!}
                                                                </div>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
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
                                            <a href="#" data-size="md" data-url="{{ route('leads.products.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Product')}}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5>{{__('Products')}}</h5>

                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Price')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($lead->products() as $product)
                                                    <tr>
                                                        <td>
                                                            {{$product->name}}
                                                        </td>
                                                        <td>
                                                            {{\Auth::user()->priceFormat($product->sale_price)}}
                                                        </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leads.products.destroy', $lead->id,$product->id]]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Form::close() !!}
                                                                </div>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
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
                                            <a href="#" data-size="md" data-url="{{ route('leads.sources.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Source')}}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5>{{__('Sources')}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($sources as $source)
                                                    <tr>
                                                        <td>{{$source->name}} </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leads.sources.destroy', $lead->id,$source->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Form::close() !!}
                                                                </div>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        @can('create lead email')
                                            <div class="float-end">
                                                <a href="#" data-size="md" data-url="{{ route('leads.emails.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Email')}}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus"></i>
                                                </a>
                                            </div>
                                        @endcan
                                        <h5>{{__('Emails')}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mt-2">
                                            @if(!$emails->isEmpty())
                                                @foreach($emails as $email)
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="{{asset('/storage/uploads/avatar/avatar.png')}}"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0">{{$email->subject}}</h6>
                                                                        <span class="text-muted text-sm">{{$email->to}}</span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        {{$email->created_at->diffForHumans()}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-center">
                                                    {{__(' No Emails Available.!')}}
                                                </li>
                                            @endif
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
                                            <a href="#" data-size="lg" data-url="{{ route('leads.discussions.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Message')}}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                        <h5>{{__('Discussion')}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush mt-2">
                                            @if(!$lead->discussions->isEmpty())
                                                @foreach($lead->discussions as $discussion)
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="@if($discussion->user->avatar) {{asset('/storage/uploads/avatar/'.$discussion->user->avatar)}} @else {{asset('/storage/uploads/avatar/avatar.png')}} @endif"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0"> {{$discussion->comment}}</h6>
                                                                        <span class="text-muted text-sm">{{$discussion->user->name}}</span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        {{$discussion->created_at->diffForHumans()}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-center">
                                                    {{__(' No Data Available.!')}}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{__('Notes')}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="summernote-simple">{!! $lead->notes !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="files" class="card">
                        <div class="card-header ">
                            <h5>{{__('Files')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                        </div>
                    </div>
                    <div id="calls" class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <a href="#" data-size="lg" data-url="{{ route('leads.calls.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Call')}}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                            <h5>{{__('Calls')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th width="">{{__('Subject')}}</th>
                                        <th>{{__('Call Type')}}</th>
                                        <th>{{__('Duration')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($calls as $call)
                                        <tr>
                                            <td>{{ $call->subject }}</td>
                                            <td>{{ ucfirst($call->call_type) }}</td>
                                            <td>{{ $call->duration }}</td>
                                            <td>{{ isset($call->getLeadCallUser) ? $call->getLeadCallUser->name : '-' }}</td>
                                            <td>
                                                @can('edit lead call')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('leads/'.$lead->id.'/call/'.$call->id.'/edit') }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Role Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete lead call')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.calls.destroy', $lead->id,$call->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="calls" class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <a href="#" data-size="lg" data-url="{{ route('leads.remind.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Reminder')}}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus"></i>
                                </a>
                            </div>
                            <h5>{{__('Reminders')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th width="">{{__('Name')}}</th>
                                        <th>{{__('Description')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Time')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reminds as $remind)
                                        <tr>
                                            <td>{{ $remind->name }}</td>
                                            <td>{{ $remind->description }}</td>
                                            <td>{{ $remind->time }}</td>
                                            <td>{{ $remind->date }}</td>
                                            <td>
                                                 
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('leads/'.$lead->id.'/remind/'.$remind->id.'/edit') }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Role Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                               <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.reminds.destroy', $lead->id,$remind->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                        {!! Form::close() !!}
                                                    </div>
                                            
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="activity" class="card">
                        <div class="card-header">
                            <h5>{{__('Activity')}}</h5>
                        </div>
                        <div class="card-body height-450">

                            <div class="row" style="height:450px !important;overflow-y: scroll;">
                                <ul class="event-cards list-group list-group-flush mt-3 w-100">
                                    @if(!$lead->activities->isEmpty())
                                        @foreach($lead->activities as $activity)
                                            <li class="list-group-item card mb-3">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avtar bg-primary">
                                                                <i class="fas {{ $activity->logIcon() }}"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                <h6 class="m-0">{!! $activity->getLeadRemark() !!}</h6>
                                                                <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">

                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        No activity found yet.
                                    @endif
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
