	@include('ui::header')

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <h1 class="navbar-brand">FatturaPA testUI - Sistema di Interscambio</h1>
      <span id="dateTime"></span>
    </nav>

    <div id="wrapper">
    
    <!-- Sidebar -->
    @include('ui::sidebar') 

    <div id="content-wrapper">
    	<div class="container-fluid" id="tables">
			<div class="row">
      			<div class="col-sm">
      				<channel-table
	              	endpoint="/rpc/channels/"
	              	description=""
	              	:home=home
	              	title="Channels">
	              	</channel-table>
      			</div>
      		</div>
    	</div>
   	</div> <!-- /.content-wrapper -->

    </div> <!-- /#wrapper -->
	
	<!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
      <span>^</span>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <script type="text/javascript" src="js/common.js"></script>
    <script type="text/javascript" src="js/InvoiceTable.js"></script>
    <script type="text/javascript" src="js/ChannelTable.js"></script>
    <script type="text/javascript" src="js/InvoiceTable2.js"></script>
    <!-- Sticky Footer -->
    <footer class="sticky-footer">
        <div class="container my-auto">
            <div class="text-center my-auto">
              <span class="copyright">
                &copy; Copyright 2018 by <a target="_blank" href="https://simevo.com/">simevo.com</a> - <a target="_blank" href="https://github.com/simevo/fatturapa-testui">source code available here</a></span>
            </div>
        </div>
    </footer>
    
    <script type="text/javascript">
      var app = new Vue({
        el: '#tables',
        data: {
          home: '/sdi'
        },
        methods: {
          dispatch: function() {
            post(this.home + '/rpc/dispatch');
          }
        }
      });
      document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM fully loaded and parsed");
      });
    </script>

      <script src="js/bootstrap-italia.bundle.min.js"></script>
    <script src="js/bootstrap-italia.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    @include('ui::footer')