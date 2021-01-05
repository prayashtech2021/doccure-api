 <!-- for datetime picker -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
      <script type="text/javascript">
    $(function () {
      $('#mindate').datetimepicker({
          minDate : new Date(),
          format : 'D-M-Y h:m a',
      });
    });
</script>

 <!-- Bootstrap core JavaScript-->
  <!-- <script src="{{URL::asset('public/vendor/jquery/jquery.min.js')}}"></script> -->
  <script src="{{URL::asset('public/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Core plugin JavaScript-->
  <script src="{{URL::asset('public/vendor/jquery-easing/jquery.easing.min.js')}}"></script>

  <!-- Page level plugin JavaScript-->
  <script src="{{URL::asset('public/vendor/chart.js/Chart.min.js')}}"></script>
  <script src="{{URL::asset('public/vendor/datatables/jquery.dataTables.js')}}"></script>
  <script src="{{URL::asset('public/vendor/datatables/dataTables.bootstrap4.js')}}"></script>

  <!-- Custom scripts for all pages-->
  <script src="{{URL::asset('public/js/sb-admin.min.js')}}"></script>

  <!-- Demo scripts for this page-->
  <script src="{{URL::asset('public/js/demo/datatables-demo.js')}}"></script>
  <script src="{{URL::asset('public/js/demo/chart-area-demo.js')}}"></script>

   <script src="{{ asset('public/plugins/jquery-validation/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/plugins/jquery-validation/dist/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/plugins/noty/js/noty.min.js') }}" type="text/javascript"></script>
    <script src="{{ URL::asset('public/plugins/data-table/datatables.js')}}"></script>