<div class="row">
<div class="col-12">
<table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">거래일</th>
            <th class="text-center">이율</th>
            <th class="text-center">이자</th>
            <th class="text-center">소득세</th>
            <th class="text-center">주민세</th>
            <th class="text-center">해당일수</th>
            <th class="text-center">잔액</th>
        </tr>
    </thead>
    <tbody>
        @for($i=0; $i<'55'; $i++)
        <tr>
            <td class="text-center">2021-01-22</td>
            <td class="text-center">5.00</td>
            <td class="text-right">{{ @number_format('10000') }}</td>
            <td class="text-right">{{ @number_format('5000') }}</td>
            <td class="text-right">{{ @number_format('2000') }}</td>
            <td class="text-center">20</td>
            <td class="text-right">{{ @number_format('3000000') }}</td>
        </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <th class="text-center"></th>
            <th class="text-center"></th>
            <th class="text-right">{{ @number_format('3000000') }}</th>
            <th class="text-right">{{ @number_format('3000000') }}</th>
            <th class="text-right">{{ @number_format('3000000') }}</th>
            <th class="text-center">100</th>
            <th class="text-center"></th>
        </tr>
    </tfoot>
</table>
</div>
<!-- /.col -->
</div>




<!-- DataTables  & Plugins -->
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="/plugins/jszip/jszip.min.js"></script>
<script src="/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- Page specific script -->
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false, 
            "autoWidth": false,
            "searching": false,
            "buttons": ["copy", "csv", "excel"]
            // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>