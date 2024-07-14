@extends('layouts.master')

@section('content')
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <nav id="tabsBox" style="display: inline-block;">
                  <div class="nav nav-pills border-bottom-0" role="tablist">
                    <a class="nav-link active" id="tab1" style='margin-right:0px; cursor: pointer;' onclick='setBank("1");'><span id='tab1'>신한은행</span></a>
                  </div>
                </nav>
                <nav id="tabsBox" style="display: inline-block;">
                  <div class="nav nav-pills border-bottom-0" role="tablist">
                    <a class="nav-link" id="tab2" style='margin-right:0px; cursor: pointer;' onclick='setBank("2");'><span id='tab2'>우리은행</span></a>
                  </div>
                </nav>
                <div style="display: inline-block; float: right; margin: 10px 10px 0 0">{!! $scrapHTML ?? '' !!}</div>
                <div class="card card-lightblue card-outline">
                    <div class="card-body" id="moAcctList1" style="height: 720px;">
                      <div class="card-body table-responsive p-0" style="height: 680px;">
                        <table class="table table-sm table-hover table-head-fixed text-nowrap">
                          <thead>
                            <tr>
                              <th>대분류</th>
                              <th>중분류</th>
                              <th>계좌명</th>
                              <th>계좌번호</th>
                              <th>최근거래일</th>
                              <th>잔액</th>
                            </tr>
                          </thead>
                          <tbody>
                          @if(isset($moAcct1))
                            @forelse( $moAcct1 as $value )
                              <tr onclick="setmoAcctTradeForm('{{ $value->no }}', 'S');">
                                <td>{{ isset($configArr['mo_acct_cd'][$value->mo_acct_cd]) ? $configArr['mo_acct_cd'][$value->mo_acct_cd]: '' }}</td>
                                <td>{{ isset($configArr['mo_acct_sub_cd'][$value->mo_acct_sub_cd]) ? $configArr['mo_acct_sub_cd'][$value->mo_acct_sub_cd]: '' }}</td>
                                <td>{{ $value->mo_bank_name }}</td>
                                <td>{{ $value->mo_bank_ssn }}</td>
                                <td>{{ $value->last_trade_date }}</td>
                                <td>{{ $value->now_money }}</td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan=6 class='text-center p-4'>등록된 법인통장이 없습니다.</td>
                              </tr>
                            @endforelse
                          @else
                            <tr>
                              <td colspan=6 class='text-center p-4'>등록된 법인통장이 없습니다.</td>
                            </tr>
                          @endif
                      </table>
                      </div>
                    </div>

                    <div class="card-body" id="moAcctList2" style="height: 720px; display:none;">
                      <div class="card-body table-responsive p-0" style="height: 680px;">
                        <table class="table table-sm table-hover table-head-fixed text-nowrap">
                          <thead>
                            <tr>
                              <th>대분류</th>
                              <th>중분류</th>
                              <th>계좌명</th>
                              <th>계좌번호</th>
                              <th>최근거래일</th>
                              <th>잔액</th>
                            </tr>
                          </thead>
                          <tbody>
                          @if(isset($moAcct2))
                            @forelse( $moAcct2 as $value )
                              <tr onclick="setmoAcctTradeForm('{{ $value->no }}', 'W');">
                                <td>{{ isset($configArr['mo_acct_cd'][$value->mo_acct_cd]) ? $configArr['mo_acct_cd'][$value->mo_acct_cd]: '' }}</td>
                                <td>{{ isset($configArr['mo_acct_sub_cd'][$value->mo_acct_sub_cd]) ? $configArr['mo_acct_sub_cd'][$value->mo_acct_sub_cd]: '' }}</td>
                                <td>{{ $value->mo_bank_name }}</td>
                                <td>{{ $value->mo_bank_ssn }}</td>
                                <td>{{ $value->last_trade_date }}</td>
                                <td>{{ $value->now_money }}</td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan=6 class='text-center p-4'>등록된 법인통장이 없습니다.</td>
                              </tr>
                            @endforelse
                          @else
                            <tr>
                              <td colspan=6 class='text-center p-4'>등록된 법인통장이 없습니다.</td>
                            </tr>
                          @endif
                      </table>
                      </div>
                    </div>

                </div>
            </div>
            <div class="col-md-8" style="margin-top:35px;">
                @include('inc/list')
            </div>
        </div>
    </div>
  </section>
  <!-- /.content -->
@endsection

@section('javascript')

<script>

  // 은행선택
  function setBank(code)
  {
    // 거래 내역 정렬옵션 및 행 색상 초기화
    $('#moAcctTradeListHeader').removeClass('bg-click');
    $('#listOrdermoAcctTrade').val('');
    $('#listOrderAscmoAcctTrade').val('');
    $('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');

    if(code == '1')
    {
      $('#moAcctList2').css('display', 'none');
      $('#moAcctList1').css('display', 'block');

      $("#tab2").removeClass("active");
      $("#tab1").addClass("active");
      
      $('#customSearchmoAcctTrade').val('');
      
      $('#searchDt').val('');
      $('#moAcctTradesearchDtString').val('');
      $('#moAcctTradesearchDtStringEnd').val('');

      $('#searchDetail').val('');
      $('#searchString').val('');

      listRefresh();
    }
    if(code == '2')
    {
      $('#moAcctList1').css('display', 'none');
      $('#moAcctList2').css('display', 'block');

      $("#tab1").removeClass("active");
      $("#tab2").addClass("active");

      $('#customSearchmoAcctTrade').val('');
      
      $('#searchDt').val('');
      $('#moAcctTradesearchDtString').val('');
      $('#moAcctTradesearchDtStringEnd').val('');

      $('#searchDetail').val('');
      $('#searchString').val('');

      listRefresh();
    }
  }

  // 계좌선택
  function setmoAcctTradeForm(code, flag)
  {
    $('#customSearchmoAcctTrade').val(code);
    $('#etcmoAcctTrade').val(flag);
    listRefresh();
  }

  // 엔터막기
  function enterClear()
  {
    $('input[type="text"]').keydown(function()
      {
        if (event.keyCode === 13)
        {
          event.preventDefault();
          listRefresh();
        };
      }
    );

    $("input[data-bootstrap-switch]").each(function()
      {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
      }
    );
  }

  enterClear();

</script>

@endsection