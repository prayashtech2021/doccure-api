@extends('layouts.app')

@section('content')
<!-- Content Wrap -->
<style>
.card {
  border: none;
  color: white;
  padding: 16px 32px;
  text-align: center;
  font-size: 16px;
  margin: 15px 2px;
  opacity: 0.6;
  transition: 0.3s;
  height:110px;
}

.card:hover {opacity: 1}
.card h4, h3, h2, h5{padding:5px !important; margin:2px !important;}
.card-blue {
  background-color: blue;
}
.card-red {
    background-color: #f4511e;
}
.card-green {
    background-color: green;
}
</style>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-red">
                <h4>Total Company</h4>
                <h2>0</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-blue">
                <h4>Total Projects</h4>
                <h2>0</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-green">
                <h4>Total Tasks</h4>
                <h2>0</h2>
            </div>
        </div>
    </div>
</div>
@endsection

