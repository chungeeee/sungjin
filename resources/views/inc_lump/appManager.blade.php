

<div id="LUMP_FORM_appManager" class="lump-forms" style="display:none">
    <div class="row">
        <div class="col-md-6">
            <select class="form-control form-control-sm selectpicker pr-0" name="branch_id" id="branch_id">
            <option value=''>부서</option>
                @php Func::printOption(Func::getBranch()); @endphp
            </select>
        </div>
        <div class="col-md-6">
            <select class="form-control form-control-sm  selectpicker" name="manage_id" id="manage_id">
            <option value=''>담당</option>
                @php Func::printOption(Func::getUserId('')); @endphp
            </select>
        </div>
    </div> 
</div>

