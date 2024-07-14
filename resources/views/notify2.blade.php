@include('inc/header')
<?
$array_notice_msg = Array();
?>

<li class="dropdown notifications-menu">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <i class="fa fa-bell-o"></i>
    <span class="label label-warning"> {{ Auth::user()->unreadNotifications->count() }} </span>
  </a>
  <!--ul class="dropdown-menu"//-->
  <ul>
    <li class="header">{{ Auth::user()->unreadNotifications->count() }} 개의 공지가 있습니다다다다.</li>
    <li>
      <ul class="menu">

      @foreach ( Auth::user()->unreadNotifications as $notis )
      <li>
        <a href="notify/notifyRead" style="font-size:12px;">
          <i class="fa fa-star"></i>
          {{ $notis->data['items']['msg'] }}
        </a>
      </li>
      @endforeach

      ********************************************************

      @foreach ( Auth::user()->readNotifications as $readNotis )
      <li>
        <a href="#" style="font-size:12px;">
          <i class="fa fa-star"></i>
          {{ $readNotis->data['items']['url'] }}
        </a>
      </li>
      @endforeach

      </ul>

    </li>
    <li class="footer"><a href="/homepage/notice">View all</a></li>
  </ul>
</li>

@include('inc/footer')
