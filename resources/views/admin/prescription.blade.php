@extends('admin.layout.dashboard')

@section('content')

<!-- start section content -->
<div class="content-body">
    <div class="warper container-fluid">
        <div class="new_prescription main_container">
            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0">
                    <div class="welcome-text">
                        <h4 class="text-primary">New Prescription</h4>
                        <p class="mb-0">Add New Prescription</p>
                    </div>
                </div>
                <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                        <li class="breadcrumb-item active"><a href="new-prescription.html">New Prescription</a>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h4 class="card-title"> Patient informations </h4>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="form-group">
                                    <select class="form-control form-select">
                                        <option>Select Patient...</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                        <option>Full Name</option>
                                    </select>
                                </div>
                                <div class="form-group text-center d-none">
                                    <img src="assets/images/patient-icon.png"
                                        class="img-profile rounded-circle img-fluid" alt="img">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Drugs list</h4>
                        </div>
                        <div class="card-body">
                            <div class="drugslist"></div>
                            <div class="form-group">
                                <a class="btn btn-primary float-end" id="butonAddDrug">Add Drug</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Tests list</h4>
                        </div>
                        <div class="card-body">
                            <div class="addTest"></div>
                            <div class="form-group">
                                <a class="btn btn-primary float-end" id="butonAddTest">Add Test</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary float-end">Create
                                    Prescription</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End section content -->

@endsection