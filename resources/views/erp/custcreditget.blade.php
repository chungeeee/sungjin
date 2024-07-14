@extends('layouts.masterPop')
@section('content')

<div class="card-body p-1">
    <img src="/img/kcb.jpg" align=absmiddle width="70" style="max-height:100%;margin-right:3px;" border=0><span class="text font-weight-bold h6 mr-1">크레딧 파트너</span>
    <table class="table table-sm table-bordered table-input text-xs">
    <!-- BODY -->
        <colgroup>
            <col width="15%"/>
            <col width="15%"/>
            <col width="15%"/>
            <col width="20%"/>
            <col width="15%"/>
            <col width="15%"/>
        </colgroup>
        <tr>
            <th class="text-center"> 이름</th>
            <td class="text-center"> {{ $ci->name }}</td>
            <th class="text-center" class="text-center">주민번호</th>
            <td class="text-center"> {{ Func::ssnFormat($ci->ssn,'Y') }}</td>
            <th class="text-center">고객번호</th>
            <td class="text-center"> {{ $ci->no }}</td>
        </tr>
        <tr>    
            <th class="text-center">조회내역</th>
            <td class="text-center">
                <select class="form-control form-control-sm text-xs "  name="integration_key" id="integration_key">
                    <option value=''>날짜</option>
                    {{ Func::printOption($array_list, '') }}
                </select>
            </td>
            <th class="text-center">조회동의</th>
            <td class="text-center">
                <select class="form-control form-control-sm text-xs "  name="info_ok" id="info_ok">
                    <option value=''>선택</option>
                    {{ Func::printOption($array_ok_type_report, '') }}
                </select>
            </td>
            </td>
            <th class="text-center">조회목적</th>
            <td class="text-center"> 
                <select class="form-control form-control-sm text-xs "  name="search_div" id="search_div">
                    <option value=''>선택</option>
                    {{ Func::printOption($array_search_report, '') }}
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="6" class="text-center " height="50">
                <button type="button" class="btn btn-xs btn-outline-info " onclick="searchKCB('A');"><i class="fa fa-plus-square text-info -1"></i>조회보기</button>
                <button type="button" class="btn btn-xs btn-outline-info " onclick="searchKCB('B');"><i class="fa fa-plus-square text-info -1"></i>실시간조회</button>
            </td>
        </tr>
    </table>
</div>
<div style="width:95%; margin: 2% 0 0 2%;">
    <span class="text-danger font-weight-bold h6 m-1">신용평점정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <colgroup>
                    <col width="25%"/>
                    <col width="25%"/>
                    <col width="25%"/>
                    <col width="25%"/>
                </colgroup>
                <tr>CB SCORE</tr>
                <tr>
                    <th class="text-center"> 구분 </th>
                    <th class="text-center"> 등급 </th>
                    <th class="text-center"> 평점 </th>
                    <th class="text-center"> 순위 </th>
                </tr>
                <tr>
                    <th class="text-center"> CB </th>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                </tr>
                <tr>
                    <th class="text-center"> 파산등급 </th>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>CB SCORE 산출요인</tr>
                <tr>
                    <th colspan="2" class="text-center"> 긍정적요인 </th>
                    <th colspan="2" class="text-center"> 부정적요인 </th>
                </tr>

            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>CB SCORE 산술 배제사유</tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
            <tr>CB SCORE 연간이력</tr>
                <tr>
                    <th rowspan="2" class="text-center"> 년/월 </th>
                    <th colspan="10" class="text-center"> 2022 </th>
                    <th colspan="2" class="text-center"> 2023 </th>
                </tr>
                <tr>
                    <th  class="text-center"> 3 </th>
                    <th  class="text-center"> 4 </th>
                    <th  class="text-center"> 5 </th>
                    <th  class="text-center"> 6 </th>
                    <th  class="text-center"> 7 </th>
                    <th  class="text-center"> 8 </th>
                    <th  class="text-center"> 9 </th>
                    <th  class="text-center"> 10 </th>
                    <th  class="text-center"> 11 </th>
                    <th  class="text-center"> 12 </th>
                    <th  class="text-center"> 1 </th>
                    <th  class="text-center"> 2 </th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <div class="card-body p-1">
        <span class="text-danger font-weight-bold h6 mr-1">변동현황 요약</span>
        <hr>
        <div style="width:95%; margin: 2% 0 0 2%;">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>신용정보 현황 요약</tr>
                <tr>
                    <th rowspan="2" class="text-center"> 구분 </th>
                    <th colspan="2" class="text-center"> 금융연체 </th>
                    <th class="text-center"> 비금융연체 </th>
                    <th colspan="2" class="text-center"> 기타연체 </th>
                    <th colspan="2" class="text-center"> 경매정보 </th>
                </tr>
                <tr>
                    <th class="text-center"> 단기 </th>
                    <th class="text-center"> 장기 </th>
                    <th class="text-center"> 장기 </th>
                    <th class="text-center"> 공공정보 </th>
                    <th class="text-center"> 금융질서문란 </th>
                    <th class="text-center"> 권리정보 </th>
                    <th class="text-center"> 의뭊어보 </th>
                </tr>
                <tr>
                    <th class="text-center"> 총건수 </th>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                </tr>
                <tr>
                    <th class="text-center"> 총금액 </th>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> 0 </td>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                    <td class="text-center"> - </td>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <div class="card-body p-1">
        <span class="text-danger font-weight-bold h6 mr-1">개인정보</span>
        <hr>
        <div style="width:95%; margin: 2% 0 0 2%;">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>개요정보</tr>
                <tr>
                    <th class="text-center"> 성명(대표자명) </th>
                    <th class="text-center"> 대표자(주민번호) </th>
                    <th class="text-center"> 연령 </th>
                    <th class="text-center"> 성별 </th>
                    <th class="text-center"> 사망자여부 </th>
                </tr>
                <tr>
                    <td class="text-center"> </td>
                    <td class="text-center">-******</td>
                    <td class="text-center">123</td>
                    <td class="text-center">여</td>
                    <td class="text-center"></td>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <span class="text-danger font-weight-bold h6 mr-1">신용정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>카드 개설정보</tr>
                <tr>
                    <th class="text-center"> 기관1 </th>
                    <th class="text-center"> 기관2 </th>
                    <th class="text-center"> 카드종류 </th>
                    <th class="text-center"> 개설일자 </th>
                </tr>
                <tr>
                    <td class="text-center"> </td>
                    <td class="text-center"> </td>
                    <td class="text-center"> </td>
                    <td class="text-center"> </td>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>대출 개설정보</tr>
                <tr>
                    <th class="text-center"> 기관1 </th>
                    <th class="text-center"> 기관2 </th>
                    <th class="text-center"> 카드개설일자 </th>
                    <th class="text-center"> 대출금액 </th>
                    <th class="text-center"> 대출종류 </th>
                    <th class="text-center"> 보증/담보/신용 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>연대보증 개설정보</tr>
                <tr>
                    <th class="text-center"> 기관명 </th>
                    <th class="text-center"> 보증약정일자 </th>
                    <th class="text-center"> 보증한도금액 </th>
                    <th class="text-center"> 보증대상금액 </th>
                    <th class="text-center"> 보증종류 </th>
                    <th class="text-center"> 보증계좌연체금액</th>
                    <th class="text-center"> 보증계좌연체일수 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>이용정보</tr>
                <tr>
                    <th rowspan="2" class="text-center"> 년/월 </th>
                    <th colspan="10" class="text-center"> 2022 </th>
                    <th colspan="2" class="text-center"> 2023 </th>
                </tr>
                <tr>
                    <th  class="text-center"> 3 </th>
                    <th  class="text-center"> 4 </th>
                    <th  class="text-center"> 5 </th>
                    <th  class="text-center"> 6 </th>
                    <th  class="text-center"> 7 </th>
                    <th  class="text-center"> 8 </th>
                    <th  class="text-center"> 9 </th>
                    <th  class="text-center"> 10 </th>
                    <th  class="text-center"> 11 </th>
                    <th  class="text-center"> 12 </th>
                    <th  class="text-center"> 1 </th>
                    <th  class="text-center"> 2 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>금융 연체정보</tr>
                <tr>
                    <th class="text-center"> 기관명 </th>
                    <th class="text-center"> 지점명 </th>
                    <th class="text-center"> 최초연체일자 </th>
                    <th class="text-center"> 최초연체금액 </th>
                    <th class="text-center"> 연체기산일자 </th>
                    <th class="text-center"> 연체금액</th>
                    <th class="text-center"> 연체상환일자 </th>
                    <th class="text-center"> 상환금액 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>대지급 정보</tr>
                <tr>
                    <th class="text-center"> 기관명 </th>
                    <th class="text-center"> 지점명 </th>
                    <th class="text-center"> 발생일자 </th>
                    <th class="text-center"> 등록금액 </th>
                    <th class="text-center"> 연체금액 </th>
                    <th class="text-center"> 차기상환일자</th>
                    <th class="text-center"> 상환금액 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>채무불이행, 금융질서문란, 공공정보(신용정보원) 연체정보</tr>
                <tr>
                    <th class="text-center"> 정보구분 </th>
                    <th class="text-center"> 보증약정일자기관명 </th>
                    <th class="text-center"> 지점명 </th>
                    <th class="text-center"> 발생일자 </th>
                    <th class="text-center"> 등록금액 </th>
                    <th class="text-center"> 연체금액</th>
                    <th class="text-center"> 해제일자 </th>
                    <th class="text-center"> 해제구분 </th>
                    <th class="text-center"> 등록사유 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>채무불이행(신용정보사), 비금융 연체정보</tr>
                <tr>
                    <th class="text-center"> 기관명 </th>
                    <th class="text-center"> 발생일자 </th>
                    <th class="text-center"> 등록금액 </th>
                    <th class="text-center"> 연체금액 </th>
                    <th class="text-center"> 해제일자 </th>
                    <th class="text-center"> 등록사유</th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <span class="text-danger font-weight-bold h6 mr-1">관보정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>파산/면책,국적/실종,금(한정)치산자 정보</tr>
                <tr>
                    <th class="text-center"> 정보구분 </th>
                    <th class="text-center"> 추처 </th>
                    <th class="text-center"> 정보종류 </th>
                    <th class="text-center"> 관보일자 </th>
                    <th class="text-center"> 사건번호 </th>
                    <th class="text-center"> 관보등급 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>면책/개인회생 정보(신용정보원)</tr>
                <tr>
                    <th class="text-center"> 출처 </th>
                    <th class="text-center"> 신용정보원 등록사유 </th>
                    <th class="text-center"> 발생일자 </th>
                    <th class="text-center"> 등록기관명 </th>
                    <th class="text-center"> 사건번호 </th>
                    <th class="text-center"> 등록번호 </th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <span class="text-danger font-weight-bold h6 mr-1">채무조정 정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>신용회복위원회</tr>
                <tr>
                    <th class="text-center"> 직업코드 </th>
                    <th class="text-center"> 신청일자 </th>
                    <th class="text-center"> 변제금납입일자 </th>
                    <th class="text-center"> 조종후최종채무액 </th>
                    <th class="text-center"> 총변제원금 </th>
                    <th class="text-center"> 변제후잔존채무액 </th>
                </tr>
                <tr>
                    <th class="text-center"> 직업명 </th>
                    <th class="text-center"> 회복지원통보일자 </th>
                    <th class="text-center"> 총변제이자 </th>
                    <th class="text-center"> 총납입회차 </th>
                    <th class="text-center"> 실납입회차 </th>
                    <th class="text-center"> 면제회차 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
            <tr>자산관리공사 (조정정보)</tr>
                <tr>
                    <th class="text-center"> 기관명 </th>
                    <th class="text-center"> 조정확정일자 </th>
                    <th class="text-center"> 조정전총채무액 </th>
                    <th class="text-center"> 조정전원금 </th>
                    <th class="text-center"> 조정전미수이자 </th>
                </tr>
                <tr>
                    <th class="text-center"> 분할상환여부 </th>
                    <th class="text-center"> 채무감면여부 </th>
                    <th class="text-center"> 조정후총채무액 </th>
                    <th class="text-center"> 조정후원금 </th>
                    <th class="text-center"> 조정후미수이자 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>자산관리공사 (입금정보)</tr>
                <tr>
                    <th class="text-center"> 최근입금일자 </th>
                    <th class="text-center"> 총입금회차 </th>
                    <th class="text-center"> 입금후잔액 </th>
                    <th class="text-center"> 입금후원금 </th>
                    <th class="text-center"> 입금후미수이자 </th>
                    <th class="text-center"> 연체기산일자 </th>
                    <th class="text-center"> 연체금액 </th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <span class="text-danger font-weight-bold h6 mr-1"> 추정 정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>소득추청정보</tr>
                <tr>
                    <th class="text-center"> 추정소득등급 </th>
                    <th class="text-center"> 추정연소득금액 </th>
                    <th class="text-center"> 신뢰수준 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>부채추정 정보</tr>
                <tr>
                    <th class="text-center"> 구분 </th>
                    <th class="text-center"> 거래년도 </th>
                    <th class="text-center"> 거래기관 </th>
                </tr>
            </table>
        </div>
        <br>
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>주거래은행 추정 정보</tr>
                <tr>
                    <th class="text-center"> 순위 </th>
                    <th class="text-center"> 기관명 </th>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <span class="text-danger font-weight-bold h6 mr-1">경매 정보</span>
    <hr>
    <div style="width:95%; margin: 2% 0 0 2%;">
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">
                <tr>
                    <th colspan="4" class="text-center"> 경매공고일 이후 정보 </th>
                    <th colspan="2" class="text-center"> 배당요구종기일 이후 정보 </th>
                </tr>
                <tr>
                    <th class="text-center"> 당사자명 </th>
                    <th class="text-center"> 관할법원 </th>
                    <th class="text-center"> 접수일자(경매신청) </th>
                    <th class="text-center"> 최종결과 </th>
                    <th class="text-center"> 물건번호 </th>
                    <th class="text-center"> 최종경매일자 </th>
                </tr>
                <tr> 					
                    <th class="text-center"> 당사자구분 </th>
                    <th class="text-center"> 사건번호 </th>
                    <th class="text-center"> 물건용도 </th>
                    <th class="text-center"> 최종결과일자 </th>
                    <th class="text-center"> 감정평가액 </th>
                    <th class="text-center"> 종국일자 </th>
                </tr>
                <tr>					
                    <th class="text-center"> 당사자주소 </th>
                    <th class="text-center"> 권리금액/청구금액 </th>
                    <th class="text-center"> 물건소재지 </th>
                    <th class="text-center"> 배당요구종기일자 </th>
                    <th class="text-center"> 감정일자 </th>
                    <th class="text-center"> 낙찰금액 </th>
                </tr>
            </table>
        </div>
    </div>
    <br>
</div>



        

    @endsection