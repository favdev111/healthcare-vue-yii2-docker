<?php

$this->title = 'Dashboard';
?>

<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Active Doctors</h4>
                </div>
                <div class="card-body">
                    <?php echo $doctorsActiveCount ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="far fa-user"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Active Patients</h4>
                </div>
                <div class="card-body">
                    <?php echo $patientsActiveCount ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Published Jobs</h4>
                </div>
                <div class="card-body">
                    <?php echo $jobsActiveCount ?>
                </div>
            </div>
        </div>
    </div>
</div>
