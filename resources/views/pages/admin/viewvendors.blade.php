@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Vendor Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item"><a href="{{ route('vendor.list') }}">Vendors List</a></li>
						<li class="breadcrumb-item active">Vendor Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <?php
        $logoDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 3);
        $logoPath = $logoDocument ? $logoDocument['pbvd_document_url'] : null;

        $parlourCertificateDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 1);
        $parlours_certificate_id = $parlourCertificateDocument ? $parlourCertificateDocument['pbvd_id'] : null;
        $parlour_certificate_url = $parlourCertificateDocument ? $parlourCertificateDocument['pbvd_document_url'] : null;
        $parlours_certification_status = $parlourCertificateDocument ? $parlourCertificateDocument['pbvd_document_status'] : null;

        $brDocDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 2);
        $br_doc_id = $brDocDocument ? $brDocDocument['pbvd_id'] : null;
        $br_doc_url = $brDocDocument ? $brDocDocument['pbvd_document_url'] : null;
        $br_doc_status = $brDocDocument ? $brDocDocument['pbvd_document_status'] : null;

        $ownersFrontNicDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 4);
        $owners_front_nic_id = $ownersFrontNicDocument ? $ownersFrontNicDocument['pbvd_id'] : null;
        $owners_front_nic_url = $ownersFrontNicDocument ? $ownersFrontNicDocument['pbvd_document_url'] : null;
        $owners_front_nic_status = $ownersFrontNicDocument ? $ownersFrontNicDocument['pbvd_document_status'] : null;

        $ownersBackNicDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 5);
        $owners_back_nic_id = $ownersBackNicDocument ? $ownersBackNicDocument['pbvd_id'] : null;
        $owners_back_nic_url = $ownersBackNicDocument ? $ownersBackNicDocument['pbvd_document_url'] : null;
        $owners_back_nic_status = $ownersBackNicDocument ? $ownersBackNicDocument['pbvd_document_status'] : null;

        $vendorLogoDocument = collect($vendor->vendorDocuments)->firstWhere('pbvd_required_document_id', 6);
        $vendor_logo_id = $vendorLogoDocument ? $vendorLogoDocument['pbvd_id'] : null;
        $vendor_logo_url = $vendorLogoDocument ? $vendorLogoDocument['pbvd_document_url'] : null;
        $vendor_logo_status = $vendorLogoDocument ? $vendorLogoDocument['pbvd_document_status'] : null;

        $vendorPhotoDocument = collect($vendor->vendorDocuments)->where('pbvd_required_document_id', 7)->values();
        // $vendor_photo_id = $vendorPhotoDocument ? $vendorPhotoDocument['pbvd_id'] : null;
        // $vendor_photo_url = $vendorPhotoDocument ? $vendorPhotoDocument['pbvd_document_url'] : null;
        // $vendor_photo_status = $vendorPhotoDocument ? $vendorPhotoDocument['pbvd_document_status'] : null;

        $vendor_created_date = $vendor->created_at;
        $curent_date_time = date('Y-m-d H:i:s');

        $start_datetime = new DateTime($vendor_created_date); 
        $diff = $start_datetime->diff(new DateTime($curent_date_time));
    ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" src="<?= $logoPath; ?>" alt="User profile picture">
                            </div>
                            <h3 class="profile-username text-center">{{ $vendor->pbv_business_name ? $vendor->pbv_business_name : 'N/A' }}</h3>
                            <p class="text-muted text-center">{{ $vendor->pbv_brno }}</p>
                            <!--ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Followers</b> <a class="float-right">1,322</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Following</b> <a class="float-right">543</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Friends</b> <a class="float-right">13,287</a>
                                </li>
                            </ul>
                            <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a-->
                        </div>
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Vendor</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Service Type</strong>
                            <p class="text-muted">{{ $vendor->pbsc_name }}</p>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                            <p class="text-muted">
                                @if($vendor->pbv_address != '')
                                    {{ $vendor->pbv_address }},
                                @endif
                                @if($vendor->pbv_city != '')
                                    {{ $vendor->pbv_city }}
                                @endif
                            </p>
                            <strong><i class="fas fa-phone-alt"></i> Contact No</strong>
                            <p class="text-muted">{{ $vendor->pbv_contactno }}</p>
                            <strong><i class="fas fa-envelope"></i> Email Address</strong>
                            <p class="text-muted">{{ $vendor->pbv_email }}</p>
                        </div>
                    </div>
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">About Owner</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Owner Name</strong>
                            <p class="text-muted">{{ $vendor->user->pbu_first_name }} {{ $vendor->user->pbu_last_name }}</p>
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                            <p class="text-muted">
                                @if($vendor->user->pbu_address != '')
                                    {{ $vendor->user->pbu_address }},
                                @endif
                                @if($vendor->user->pbu_city != '')
                                    {{ $vendor->user->pbu_address }}
                                @endif
                            </p>
                            <strong><i class="fas fa-phone-alt"></i> Contact No</strong>
                            <p class="text-muted">{{ $vendor->user->pbu_mobileno }}</p>
                            <strong><i class="fas fa-envelope"></i> Email Address</strong>
                            <p class="text-muted">{{ $vendor->user->pbu_email }}</p>
                        </div>
                    </div>
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">About Bank Info</h3>
                            <a href="#"class="float-right" data-toggle="modal" data-target="#editBankModal"><i class="fas fa-pen"></i></a>
                        </div>
                        <div class="card-body">
                            @if($vendor->bankInfo)
                                @foreach($vendor->bankInfo as $bankInfo)
                                    <strong><i class="fas fa-envelope"></i> Account Holder Name</strong>
                                    <p class="text-muted">{{ $bankInfo->pbvb_holder_name }}</p>
                                    <strong><i class="fas fa-book mr-1"></i> Bank Name</strong>
                                    <p class="text-muted">{{ $bankInfo->bank->pbb_name }}</p>
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Branch</strong>
                                    <p class="text-muted">{{ $bankInfo->pbvb_branch }}</p>
                                    <strong><i class="fas fa-phone-alt"></i> Account No</strong>
                                    <p class="text-muted">{{ $bankInfo->pbvb_accountno }}</p>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                            class="custom-control-input" 
                                            id="customSwitch{{ $bankInfo->pbvb_id }}" 
                                            {{ $bankInfo->pbvb_status == 1 ? 'checked' : '' }}
                                            onchange="updateBankStatus({{ $bankInfo->pbvb_id }}, this.checked)">
                                        <label class="custom-control-label" for="customSwitch{{ $bankInfo->pbvb_id }}">Account Status</label>
                                    </div>
                                @endforeach
                            @else
                                <p>Bank Info is Empty</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#attachments" data-toggle="tab">Attachments</a></li>
                                <li class="nav-item"><a class="nav-link" href="#services" data-toggle="tab">Services</a></li>
                                <!--li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab" data-value="{{ $vendor->pbv_uid }}" id="activity_logs">Activity</a></li>
                                <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li-->
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="attachments">
                                    @php                                        
                                        // Status text helper
                                        function getStatusBadge($status){
                                            switch($status){
                                                case 1: return '<span class="badge badge-info">Under Review</span>';
                                                case 2: return '<span class="badge badge-warning">Pending</span>';
                                                case 3: return '<span class="badge badge-success">Approved</span>';
                                                case 4: return '<span class="badge badge-danger">Rejected</span>';
                                                default: return '';
                                            }
                                        }
                                    @endphp
                                    @foreach($requiredDocuments as $doc)

                                        @php
                                            $document = collect($vendor->vendorDocuments)
                                                        ->where('pbvd_required_document_id', $doc->pbrd_id);

                                            // If single file
                                            if($doc->pbrd_is_single){
                                                $document = $document->first();
                                            }

                                        @endphp

                                        <div class="post clearfix">
                                            <div class="user-block">
                                                <span class="username">
                                                    <a href="#">{{ $vendor->pbv_business_name ? $vendor->pbv_business_name : 'N/A' }}</a>
                                                </span>
                                                <span class="description">
                                                    {{ $vendor->created_at }}
                                                </span>
                                            </div>

                                            <div>
                                                <h5>{{ $doc->pbrd_label }}</h5>

                                                {{-- SINGLE DOCUMENT --}}
                                                @if($doc->pbrd_is_single)

                                                    @if($document)

                                                        {!! getStatusBadge($document->pbvd_document_status) !!} - {{ $document->pbvd_document_status == 4 ? $document->pbvd_document_extra : '' }}
                                                        <br>
                                                        <br>
                                                        @if(in_array($document->pbvd_document_status, [1, 2, 4]) && $vendor->pbv_status != 2)
                                                            <button class="btn btn-success approve-btn"
                                                                data-document-id="{{ $document->pbvd_id }}">
                                                                Approve
                                                            </button>

                                                            <button class="btn btn-danger reject-btn"
                                                                data-document-id="{{ $document->pbvd_id }}">
                                                                Reject
                                                            </button>
                                                        @endif

                                                        <embed src="{{ $document->pbvd_document_url }}" width="100%" height="420px" />

                                                    @else
                                                        <div class="alert alert-warning">No document uploaded</div>
                                                        @if($vendor->pbv_status != 2)
                                                            <form action="{{ route('vendor.document.upload') }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="vendor_id" value="{{ $vendor->pbv_id }}">
                                                                <input type="hidden" name="document_type_id" value="{{ $doc->pbrd_id }}">

                                                                <input type="file" name="document" class="form-control mb-2" required>

                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    Upload Document
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif

                                                {{-- MULTIPLE DOCUMENTS (like gallery) --}}
                                                @else

                                                    @if($document->count() > 0)
                                                        <div class="row">
                                                            @foreach($document as $file)
                                                                <div class="col-md-3 mb-3">
                                                                    <img src="{{ $file->pbvd_document_url }}"
                                                                        class="img-fluid rounded"
                                                                        style="height:150px;object-fit:cover;">

                                                                    {!! getStatusBadge($file->pbvd_document_status) !!} - {{ $file->pbvd_document_status == 4 ? $file->pbvd_document_extra : '' }}
                                                                    <br>
                                                                    <br>
                                                                    @if(in_array($file->pbvd_document_status, [1, 2, 4]) && $vendor->pbv_status != 2)
                                                                        <button class="btn btn-success btn-sm approve-btn w-100 mt-1"
                                                                            data-document-id="{{ $file->pbvd_id }}">
                                                                            Approve
                                                                        </button>

                                                                        <button class="btn btn-danger btn-sm reject-btn w-100 mt-1"
                                                                            data-document-id="{{ $file->pbvd_id }}">
                                                                            Reject
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-info">No files uploaded</div>
                                                        @if($vendor->pbv_status != 2)
                                                            <form action="{{ route('vendor.document.upload') }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="vendor_id" value="{{ $vendor->pbv_id }}">
                                                                <input type="hidden" name="document_type_id" value="{{ $doc->pbrd_id }}">

                                                                <input type="file" name="documents[]" multiple class="form-control mb-2" required>

                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    Upload Files
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                    @endforeach
                                </div>
                                <div class="tab-pane" id="services">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Vendor's Service List</h3>
                                        </div>
                                        <div class="card-body">
                                            <table id="example1" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Service Name</th>
                                                        <th>Price</th>
                                                        <th>Duration</th>
                                                        <th>Employes</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($vendor->services as $service)
                                                        <tr id="{{ $service->pbs_id}}">
                                                            <td>
                                                                <h5 style="text-transform: capitalize;display:block">{{ $service->pbs_name }}</h5>
                                                                <span class="badge badge-info">{{ $service->serviceFor->pbsf_name }}</span> | <span class="badge badge-secondary">{{ $service->serviceType->pbst_name }}</span>
                                                            </td>
                                                            <td>{{ $service->pbs_price }}</td>
                                                            <td>{{ $service->pbs_duration }} mins</td>
                                                            <td>{{ ($service->pbs_employes) ? $service->pbs_employes : 'N/A' }}</td>
                                                            <td>
                                                                @if($service->pbs_status == 1)
                                                                    <span class="badge badge-success">Active</span>
                                                                @else
                                                                    <span class="badge badge-secondary">Inactive</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-warning edit-service"
                                                                    data-service-id="{{ $service->pbs_id }}">
                                                                    <i class="fas fa-pen "></i> Edit
                                                                </button>
                                                                <button class="btn btn-danger btn-sm delete-service-btn" data-service-id="{{ $service->pbs_id }}"><i class="fas fa-trash"></i> Delete</button>
                                                            </td>
                                                        </tr>                         
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--div class="tab-pane" id="activity">
                                    <div class="post">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm" src="dist/img/user1-128x128.jpg" alt="user image">
                                            <span class="username">
                                                <a href="#">Jonathan Burke Jr.</a>
                                                <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                                            </span>
                                            <span class="description">Shared publicly - 7:30 PM today</span>
                                        </div>
                                        <p>
                                            Lorem ipsum represents a long-held tradition for designers,
                                            typographers and the like. Some people hate it and argue for
                                            its demise, but others ignore the hate as they create awesome
                                            tools to help create filler text for everyone from bacon lovers
                                            to Charlie Sheen fans.
                                        </p>
                                        <p>
                                            <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                                            <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                                            <span class="float-right">
                                                <a href="#" class="link-black text-sm">
                                                    <i class="far fa-comments mr-1"></i> Comments (5)
                                                </a>
                                            </span>
                                        </p>
                                        <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                                    </div>
                                    <div class="post clearfix">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm" src="dist/img/user7-128x128.jpg" alt="User Image">
                                            <span class="username">
                                                <a href="#">Sarah Ross</a>
                                                <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                                            </span>
                                            <span class="description">Sent you a message - 3 days ago</span>
                                        </div>
                                        <p>
                                            Lorem ipsum represents a long-held tradition for designers,
                                            typographers and the like. Some people hate it and argue for
                                            its demise, but others ignore the hate as they create awesome
                                            tools to help create filler text for everyone from bacon lovers
                                            to Charlie Sheen fans.
                                        </p>
                                        <form class="form-horizontal">
                                            <div class="input-group input-group-sm mb-0">
                                                <input class="form-control form-control-sm" placeholder="Response">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-danger">Send</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="post">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm" src="dist/img/user6-128x128.jpg" alt="User Image">
                                            <span class="username">
                                                <a href="#">Adam Jones</a>
                                                <a href="#" class="float-right btn-tool"><i class="fas fa-times"></i></a>
                                            </span>
                                            <span class="description">Posted 5 photos - 5 days ago</span>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <img class="img-fluid" src="dist/img/photo1.png" alt="Photo">
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <img class="img-fluid mb-3" src="dist/img/photo2.png" alt="Photo">
                                                        <img class="img-fluid" src="dist/img/photo3.jpg" alt="Photo">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <img class="img-fluid mb-3" src="dist/img/photo4.jpg" alt="Photo">
                                                        <img class="img-fluid" src="dist/img/photo1.png" alt="Photo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p>
                                            <a href="#" class="link-black text-sm mr-2"><i class="fas fa-share mr-1"></i> Share</a>
                                            <a href="#" class="link-black text-sm"><i class="far fa-thumbs-up mr-1"></i> Like</a>
                                            <span class="float-right">
                                                <a href="#" class="link-black text-sm">
                                                    <i class="far fa-comments mr-1"></i> Comments (5)
                                                </a>
                                            </span>
                                        </p>
                                        <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                                    </div>
                                </div>
                                <div class="tab-pane" id="timeline">
                                    <div class="timeline timeline-inverse">
                                        <div class="time-label">
                                            <span class="bg-danger">10 Feb. 2014</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-envelope bg-primary"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 12:05</span>
                                                <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>
                                                <div class="timeline-body">
                                                    Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                                    weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                                    jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                                    quora plaxo ideeli hulu weebly balihoo...
                                                </div>
                                                <div class="timeline-footer">
                                                    <a href="#" class="btn btn-primary btn-sm">Read more</a>
                                                    <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <i class="fas fa-user bg-info"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>
                                                <h3 class="timeline-header border-0"><a href="#">Sarah Young</a> accepted your friend request</h3>
                                            </div>
                                        </div>
                                        <div>
                                            <i class="fas fa-comments bg-warning"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>
                                                <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>
                                                <div class="timeline-body">
                                                    Take me to your leader!
                                                    Switzerland is small and neutral!
                                                    We are more like Germany, ambitious and misunderstood!
                                                </div>
                                                <div class="timeline-footer">
                                                    <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="time-label">
                                            <span class="bg-success">3 Jan. 2014</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-camera bg-purple"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 2 days ago</span>
                                                <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>
                                                <div class="timeline-body">
                                                    <img src="https://placehold.it/150x100" alt="...">
                                                    <img src="https://placehold.it/150x100" alt="...">
                                                    <img src="https://placehold.it/150x100" alt="...">
                                                    <img src="https://placehold.it/150x100" alt="...">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <i class="far fa-clock bg-gray"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="settings">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" id="inputName" placeholder="Name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" id="inputEmail" placeholder="Email">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-2 col-form-label">Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputName2" placeholder="Name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputExperience" class="col-sm-2 col-form-label">Experience</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control" id="inputExperience" placeholder="Experience"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputSkills" class="col-sm-2 col-form-label">Skills</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputSkills" placeholder="Skills">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox"> I agree to the <a href="#">terms and conditions</a>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-danger">Submit</button>
                                            </div>
                                        </div>
                                    </form>
                                </div-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Bank Info Update-->
            <div class="modal fade" id="editBankModal" tabindex="-1" role="dialog" aria-labelledby="editBankModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBankModalLabel">Edit Bank Information</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editBankForm" method="POST" action="{{ route('vendor.bank.update') ?? '/vendor/bank/update' }}" autocomplete="off">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendor->pbv_id ?? '' }}">
                                <input type="hidden" name="bank_info_id" id="bank_info_id" value="{{ $vendor->bankInfo->first()->pbvb_id ?? '' }}">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bank_name">Bank Name</label>
                                            <select class="form-control" id="bank_name" name="bank_name" required>
                                                <option value="">Select Bank</option>
                                                @foreach($banklist as $bank)
                                                <option value="{{ $bank->pbb_id }}" {{ ($vendor->bankInfo->first()->pbvb_bankname ?? '') == $bank->pbb_id ? 'selected' : '' }}>
                                                    {{ $bank->pbb_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="account_holder">Account Holder Name</label>
                                            <input type="text" class="form-control" id="account_holder" name="account_holder" 
                                                value="{{ $vendor->bankInfo->first()->pbvb_holder_name ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="account_number">Account Number</label>
                                            <input type="text" class="form-control" id="account_number" name="account_number" 
                                                value="{{ $vendor->bankInfo->first()->pbvb_accountno ?? '' }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="branch">Branch</label>
                                            <input type="text" class="form-control" id="branch" name="branch" 
                                                value="{{ $vendor->bankInfo->first()->pbvb_branch ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="saveBankBtn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Rejection Reason</h5>                            
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="rejectForm">
                            <div class="modal-body">
                                <input type="hidden" name="document_id" id="reject_document_id">
                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label">Please provide reason for rejection:</label>
                                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                                    <div class="invalid-feedback">Please provide a rejection reason.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-danger">Submit Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--Vendor Service Update-->
            <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editServiceForm" method="POST" action="{{ route('vendor.service.update') ?? '/vendor/service/update' }}" autocomplete="off">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $vendor->pbv_id ?? '' }}">
                                <input type="hidden" name="edit_service_id" id="edit_service_id">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="service_for">Service For</label>
                                            <select class="form-control" id="service_for" name="service_for" required>
                                                <option value="">Select Service For</option>
                                                @foreach($serviceForList as $serviceFor)
                                                <option value="{{ $serviceFor->pbsf_id }}">
                                                    {{ $serviceFor->pbsf_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="service_type">Service Type</label>
                                            <select class="form-control" id="service_type" name="service_type" required>
                                                <option value="">Select Service Type</option>
                                                @foreach($serviceTypeList as $serviceType)
                                                <option value="{{ $serviceType->pbst_id }}">
                                                    {{ $serviceType->pbst_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="service_name">Service Name</label>
                                            <input type="text" class="form-control" id="service_name" name="service_name" 
                                                value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="staff_count">Eligible Staff Count</label>
                                            <input type="text" class="form-control" id="staff_count" name="staff_count" 
                                                value="" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="service_description">Service Description</label>
                                            <input type="text" class="form-control" id="service_description" name="service_description" 
                                                value="" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="service_duration">Duration</label>
                                            <input type="text" class="form-control" id="service_duration" name="service_duration" 
                                                value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="service_price">Price</label>
                                            <input type="text" class="form-control" id="service_price" name="service_price" 
                                                value="" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="saveServiceBtn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--?php } ?-->
</div>
<script>
    $(document).ready(function(){
        $('#activity_logs').click(function(e){
            $vuid = $(this).data('value');
            $.ajax({
                type: 'GET',
                url: '/get_service_type/' + vuid,
                dataType: 'json',
                success: function(data) {
                    // console.log(data.pbsc_id);
                    $('#editServiceType #editServiceTypeID').val(data.pbst_id);
                    $('#editServiceType #editServiceType').val(data.pbst_name);
                }
            });
        });

        $('#editBankForm').on('submit', function(e) {
            e.preventDefault();

            // Show loading
            $('#saveBankBtn').html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Close modal
                        $('#editBankModal').modal('hide');
                        
                        // Refresh the page or update the displayed data
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join('\n');
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                },
                complete: function() {
                    $('#saveBankBtn').html('Save Changes').prop('disabled', false);
                }
            });
        });        

        //Approve Document
        $('.approve-btn').on('click', function() {
            var documentId = $(this).data('document-id');
            var $button = $(this);

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to approve this document.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("vendor.document.approve") }}',
                        type: 'GET',
                        data: {
                            document_id: documentId
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Approved!',
                                    response.message,
                                    'success'
                                );
                                $button.closest('.document-item').find('.document-status').text('Approved');
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'An error occurred while approving the document.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Reject Document Modal
        $('.reject-btn').on('click', function() {
            var documentId = $(this).data('document-id');
            $('#rejectModal #reject_document_id').val(documentId);

            $('#rejectModal').modal('show');
        });

        // Handle reject form submission
        $('#rejectForm').on('submit', function(e) {
            e.preventDefault();
            
            var documentId = $('#reject_document_id').val();
            var rejectionReason = $('#rejection_reason').val();
            var $rejectBtn = $('.reject-btn[data-document-id="' + documentId + '"]');
            var $postContainer = $rejectBtn.closest('.post');
            
            if (!rejectionReason.trim()) {
                $('#rejection_reason').addClass('is-invalid');
                return;
            }
            
            // Disable submit button
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: '{{ route("vendor.document.reject") }}',
                type: 'POST',
                data: {
                    document_id: documentId,
                    rejection_reason: rejectionReason,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Hide modal
                        $('#rejectModal').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Replace buttons with rejected badge
                        $postContainer.find('.approve-btn, .reject-btn').remove();
                        $postContainer.find('h5').after('<span class="badge bg-danger">Rejected</span>');
                        
                        // Reset form
                        $('#rejectForm')[0].reset();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.log('Error:', xhr);
                    let message = 'An error occurred while rejecting the document.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', message, 'error');
                },
                complete: function() {
                    $('#rejectForm button[type="submit"]').prop('disabled', false).html('Submit Rejection');
                }
            });
        });

        // Reset form when modal is closed
        $('#rejectModal').on('hidden.bs.modal', function() {
            $('#rejectForm')[0].reset();
            $('#rejection_reason').removeClass('is-invalid');
        });

        //edit service modal population
        $('.edit-service').on('click', function() {
            var serviceId = $(this).data('service-id');
            $.ajax({
                url: '{{ route("vendor.service.get") }}',
                type: 'GET',
                data: {
                    service_id: serviceId
                },
                success: function(response) {
                    if (response.success) {
                        var service = response.data;
                        $('#editServiceForm #edit_service_id').val(service.pbs_id);
                        $('#editServiceForm #service_for').val(service.pbs_service_for);
                        $('#editServiceForm #service_type').val(service.pbs_service_type);
                        $('#editServiceForm #service_name').val(service.pbs_name);
                        $('#editServiceForm #staff_count').val(service.pbs_employees ? service.pbs_employees : 0);
                        $('#editServiceForm #service_description').val(service.pbs_description);
                        $('#editServiceForm #service_duration').val(service.pbs_duration);
                        $('#editServiceForm #service_price').val(service.pbs_price);
                        
                        $('#editServiceModal').modal('show');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'An error occurred while fetching service details.', 'error');
                }
            });
        });

        $('.saveServiceBtn').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: $('#editServiceForm').attr('action'),
                type: 'POST',
                data: $('#editServiceForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        $('#editServiceModal').modal('hide');
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'An error occurred while updating the service.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', message, 'error');
                }
            });
        });
    });
    
    function updateBankStatus(bankInfoId, isChecked) {
        // Convert boolean to integer (1 for active, 0 for inactive)
        const status = isChecked ? 1 : 0;
        
        // Show loading indicator (optional)
        Swal.fire({
            title: 'Updating...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Send AJAX request
        fetch('{{ route("vendor.bank.status.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                bank_info_id: bankInfoId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                // Show error message and revert the switch
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to update status'
                });
                
                // Revert the switch to its previous state
                document.getElementById('customSwitch' + bankInfoId).checked = !isChecked;
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating status'
            });
            
            // Revert the switch
            document.getElementById('customSwitch' + bankInfoId).checked = !isChecked;
        });
    }
</script>
@stop