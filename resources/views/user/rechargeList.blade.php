@extends('user.layouts')

@section('css')
    <link href="/assets/pages/css/pricing.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <style>
        .fancybox > img {
            width: 75px;
            height: 75px;
        }
    </style>
@endsection
@section('title', '控制面板')
@section('content')
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content" style="padding-top:0;">
        <!-- BEGIN PAGE BASE CONTENT -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> 充值记录 </span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column">
                                <thead>
                                <tr>
                                    <th style="text-align: center;"> 订单号 </th>
                                    <th style="text-align: center;"> 金额 </th>
                                    <th style="text-align: center;"> 日期 </th>
                                    <th style="text-align: center;"> 状态 </th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($rechargeList->isEmpty())
                                    <tr>
                                        <td colspan="4">暂无数据</td>
                                    </tr>
                                @else
                                    @foreach($rechargeList as $key => $goods)
                                        <tr class="odd gradeX">
                                            <td style="text-align: center;">{{$goods->ss_order}}</td>
                                            <td style="text-align: center;"> {{$goods->price}} 元 </td>
                                            <td style="text-align: center;"> {{$goods->created_at}} </td>
                                            <!--<td> {{$goods->score}} </td>-->

                                            @if($goods->state == 1)
                                                <td style="text-align: center;"><span class="badge badge-success">充值完成</span></td>
                                            @else
                                                <td style="text-align: center;"><span class="badge badge-danger">未付款</span></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-4">
                                <div class="dataTables_info" role="status" aria-live="polite">共 {{$rechargeList->total()}} 条记录</div>
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <div class="dataTables_paginate paging_bootstrap_full_number pull-right">
                                    {{ $rechargeList->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE BASE CONTENT -->
    </div>
    <!-- END CONTENT BODY -->
@endsection
@section('script')
    <script src="/assets/global/plugins/fancybox/source/jquery.fancybox.js" type="text/javascript"></script>

    <script type="text/javascript">

    </script>
@endsection
