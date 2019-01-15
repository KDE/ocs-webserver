/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/
$(document).ready(function () {

    var parseTime = d3.timeParse("%Y%m"); 
    window.selectedCatid = 0;
    window.selectedTab = '';
    window.selectedMonth;

    var indicator = '<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>';
    function createMonthFilter(selectid, triggerEvent) {
        let html = '<select id="' + selectid + '" style="float: left;">';
        for (i = 1; i < 10; i++) {
            let oneMonthsAgo = moment().subtract(i, 'months');
            if (i == 1) {
                html = html + '<option selected>' + oneMonthsAgo.format('YYYYMM') + '</option>';
            } else {
                html = html + '<option>' + oneMonthsAgo.format('YYYYMM') + '</option>';
            }
        }
        html = html + '</select>';
        return html;
    }

    function createMonthFilterWithCurrent(selectid, triggerEvent) {
        let html = '<select id="' + selectid + '" style="float: left;">';
        for (i = 0; i < 10; i++) {
            let oneMonthsAgo = moment().subtract(i, 'months');
            if (i == 0) {
                html = html + '<option selected>' + oneMonthsAgo.format('YYYYMM') + '</option>';
            } else {
                html = html + '<option>' + oneMonthsAgo.format('YYYYMM') + '</option>';
            }
        }
        html = html + '</select>';
        return html;
    }

    $.ajaxSetup({
        // Disable caching of AJAX responses
        // Used when debugging
        cache: false
    });

    $.getScript("/theme/flatui/js/stati/newUserProject.js");
    $(".newuserprojectTab").click(function () {
        $.getScript("/theme/flatui/js/stati/newUserProject.js");
    });


    $(".downloadsdailyTab").click(function () {
        $('#downloadsdailyTabContainer').empty();
        $.getScript("/theme/flatui/js/stati/downloadsdaily.js");
    });
    $(".newproductsweeklyTab").click(function () {
        $('#newproductsweekly').empty();
        $.getScript("/theme/flatui/js/stati/newproductsweekly.js");
    });
    $('#numofmonthback').change(function () {
        $.getScript("/theme/flatui/js/stati/downloadsdaily.js");
    });


    $('button.filterDownloadsDomainDate').click(function () {
        loadDownloadsDomain();
    });

    function loadDownloadsDomain() {
        $('#downloadsDomainTabContainer').empty();
        let dateBegin = $("#filterDownloadsDomainDateBegin").val();
        let dateEnd = $("#filterDownloadsDomainDateEnd").val();
        $.getJSON("/backend/index/getdownloadsdomain?dateBegin=" + dateBegin + "&dateEnd=" + dateEnd, function (response) {
            let data = response.results;
            let table = "<table class='tablestati'><thead><tr><td class='number'>CNT</td><td>Domain</td><td class='number'>IS_OWN?</td></tr></thead>";
            $.each(data, function (index, value) {
                table = table + '<tr>'
                    + '<td class="number">' + value.cnt + '</td>'
                    + '<td >' + value.referer_domain + '</td>'
                    + '<td class="number">' + value.is_from_own_domain + '</td>'
                    + '</tr>';
            });
            table = table + "</table>";
            $('#downloadsDomainTabContainer').append(table);
        });
    }

    $(".downloadsDomainTab").click(function () {
        loadDownloadsDomain();
    });


    // topdownloadsperdate

    $('button.filterTopDownloadsDate').click(function () {
        loadTopdownloadsperday();
    });
    $('button.filterTopDownloadsDateClear').click(function () {
        $('#topDownloadsPerDayTabContainer').empty();
    });

    function loadTopdownloadsperday() {
        let date = $("#filterTopDownloadsDate").val();
        $.getJSON("/backend/index/gettopdownloadsperdate?date=" + date, function (response) {
            let data = response.results;
            let table = "<table class='tablestati'><thead><tr><td colspan='7'>" + date + "</td></tr><tr><td>ProjectID</td><td>Username</td><td class='number'>CNT</td><td>Title</td><td>Category</td><td>created_at</td></tr></thead>";
            $.each(data, function (index, value) {
                table = table + '<tr>'
                    + '<td><a target="_blank" href="https://opendesktop.org/p/' + value.project_id + '">'
                    + value.project_id + '</a></td>'
                    + '<td>' + value.username + '</td>'
                    + '<td class="number">' + value.cnt + '</td>'
                    + '<td>' + value.ptitle + '</td>'
                    + '<td>' + value.ctitle + '</td>'
                    + '<td>' + value.pcreated_at + '</td>'
                    + '</tr>';
            });
            table = table + "</table>";
            $('#topDownloadsPerDayTabContainer').append(table);
        });
    } 

    function loadTopdownloadspermonth() {        
        console.log(window.selectedMonth);        
        $('#topDownloadsPerMonthTabContainer').empty();
        $('#topDownloadsPerMonthTabContainer').append(indicator);
        $.getJSON("/backend/index/gettopdownloadspermonth?month=" + window.selectedMonth+'&catid='+window.selectedCatid, function (
            response) {
            let data = response.results;
            let table = "<table class='tablestati'><thead><tr><td colspan='7'>" + window.selectedMonth+'_'+window.selectedCatTitle + "</td></tr><tr><td>ProjectID</td><td>Username</td><td class='number'>CNT</td><td>Title</td><td>Category</td><td>created_at</td></tr></thead>";
            $.each(data, function (index, value) {
                table = table + '<tr>'
                    + '<td><a class="loadTopdownloadspermonthDetail" data-pid="'+value.project_id+'">'
                    + value.project_id + '</a></td>'
                    + '<td>' + value.username + '</td>'
                    + '<td class="number">' + value.cnt + '</td>'
                    + '<td><a target="_blank" href="https://opendesktop.org/p/' + value.project_id + '">' + value.ptitle + '</a></td>'
                    + '<td>' + value.ctitle + '</td>'
                    + '<td>' + value.pcreated_at + '</td>'
                    + '</tr>';
            });
            table = table + "</table>";
            $('#topDownloadsPerMonthTabContainer').empty();
            $('#topDownloadsPerMonthTabContainer').append(table);

            $('.loadTopdownloadspermonthDetail').on('click',function(){
                console.log($(this).attr("data-pid"));
                var project_id = $(this).attr("data-pid");
                loadTopdownloadspermonth_daily(project_id);
            });
        });
    } 

    function loadTopdownloadspermonth_daily(project_id){
        $('#topDownloadsPerMonthTabContainer').append('<div class="chart-wrapper" id="topDownloadsPerMonthTabContainer_DetailMonthly"></div>').append('<div class="chart-wrapper" id="topDownloadsPerMonthTabContainer_DetailDayly"></div>');

        window.project_id = project_id;
        $.getScript("/theme/flatui/js/stati/productMonthly.js");                
        $.getScript("/theme/flatui/js/stati/productDayly.js");   
    }

    $(".topDownloadsPerDayTab").click(function () {
        $('#topDownloadsPerDayTabContainer').empty();
        loadTopdownloadsperday();
    });

    $('#payoutTab').prepend(createMonthFilter('selectmonth'));
    $('#selectmonth').change(function () {
        $.getScript("/theme/flatui/js/stati/memberPayout.js");
    });
    $(".payoutTab").click(function () {
        $('#detailContainer').empty();
        $.getScript("/theme/flatui/js/stati/payoutyear.js");
        $.getScript("/theme/flatui/js/stati/memberPayout.js");
    });

    // payout Category Monthly tab begin
    $('#payoutCategoryMonthlyTab').prepend(createMonthFilter('selectmonthCategoryMonthly'));
    $(".payoutCategoryMonthlyTab").click(function () {
        $.getScript("/theme/flatui/js/stati/payoutCategoryMonthly.js");
    });
    $('#selectmonthCategoryMonthly').change(function () {
        $('.payoutCategoryMonthlyTab').trigger('click');
    });
    // payout Category Monthly tab end


    $('#payoutNewcomerTab').prepend(createMonthFilter('selectmonthNewcomer'));
    $('#selectmonthNewcomer').change(function () {
        $(".payoutNewcomerTab").trigger('click');
    });
    $(".payoutNewcomerTab").click(function () {
        $('#payoutNewcomerTabContainer').empty();
        let yyyymm = $("#selectmonthNewcomer option:selected").text();
        $.getJSON("/backend/index/newcomer?yyyymm=" + yyyymm, function (response) {
            let data = response.results;
            let table = "<table class='tablestati'>";
            $.each(data, function (index, value) {
                table = table + '<tr><td><a target="_blank" href="https://opendesktop.org/member/' + value.member_id + '">' + value.member_id + '</a></td><td>' + value.username + '</td><td>' + value.paypal_mail + '</td><td class="number">' + value.amount + '</td></tr>';
            });
            table = table + "</table>";
            $('#payoutNewcomerTabContainer').html(table);
        });
    });

    $('#payoutNewloserTab').prepend(createMonthFilter('selectmonthNewloser'));
    $('#selectmonthNewloser').change(function () {
        $(".payoutNewloserTab").trigger('click');
    });
    $(".payoutNewloserTab").click(function () {
        $('#payoutNewloserTabContainer').empty();
        let yyyymm = $("#selectmonthNewloser option:selected").text();
        $.getJSON("/backend/index/newloser?yyyymm=" + yyyymm, function (response) {
            let data = response.results;
            let table = "<table class='tablestati'>";
            $.each(data, function (index, value) {
                table = table + '<tr><td><a target="_blank" href="https://opendesktop.org/member/' + value.member_id + '">' + value.member_id + '</a></td><td>' + value.username + '</td><td>' + value.paypal_mail + '</td><td class="number">' + value.amount + '</td></tr>';
            });
            table = table + "</table>";
            $('#payoutNewloserTabContainer').html(table);
        });
    });


    $('#payoutMonthDiffTab').prepend(createMonthFilter('selectmonthMonthDiff'));
    $('#selectmonthMonthDiff').change(function () {
        $(".payoutMonthDiffTab").trigger('click');
    });
    $(".payoutMonthDiffTab").click(function () {
        $('#payoutMonthDiffTabContainer').empty();
        let yyyymm = $("#selectmonthMonthDiff option:selected").text();
        $.getJSON("/backend/index/monthdiff?yyyymm=" + yyyymm, function (response) {
            let data = response.results;
            let table = "<table class='tablestati'>";
            table = table + '<thead><tr><td>Member</td><td>Username</td><td class="number">Diff</td><td>month</td><td class="number">amount</td><td>last month</td><td  class="number">amount </td></tr></thead>';
            $.each(data, function (index, value) {
                table = table + '<tr><td><a target="_blank" href="https://opendesktop.org/member/' + value.member_id + '">' + value.member_id + '</a></td><td>' + value.username + '</td><td class="number">' + value.am_diff + '</td>'
                    + '<td>' + value.ym_akt + '</td><td class="number">' + value.am_akt + '</td>'
                    + '<td>' + value.ym_let + '</td><td class="number">' + value.am_let + '</td>'
                ;
            });
            table = table + "</table>";
            $('#payoutMonthDiffTabContainer').html(table);
        });
    });

            $('#filterTopDownloadsMonth').MonthPicker({ StartYear: 2018, ShowIcon: true,MonthFormat:'yymm',OnAfterChooseMonth: function() { 
                        window.selectedMonth = $(this).val();
                }  });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href");
                console.log(target);
                if('#topDownloadsPerMonthTab' == target || '#payoutCategoryTab' ==target)
                {
                    var cattree = $('#category-tree-container');
                    $(target).find('.filter').append(cattree);                   
                } 
                window.selectedTab = target;
                window.selectedCatid = 0;
                window.selectedCatTitle = '';
                
            });

            $('body').on('click', '#category-tree a', function (event) {
                event.preventDefault();
                event.stopPropagation();            
                var start = this.href.indexOf("cat");
                var catid;
                var title;
                if(start<0) {
                    catid = 0;
                    title = 'All';
                }else
                {
                    catid = this.href.substring(start+4, (this.href.length-1));  
                    title = $(this).text();
                }
                window.selectedCatid = catid;
                window.selectedCatTitle = title;
                if(window.selectedTab=='#payoutCategoryTab')
                {
                    if($('#payoutCategoryLineChart').find('#payoutCategoryLineChart'+catid).length==0)
                    {
                        $('#payoutCategoryLineChart').append('<div class="chart-wrapper" id="payoutCategoryLineChart'+catid+'"> loading ... </div>');
                        $.getScript("/theme/flatui/js/stati/payoutCategory.js");
                    }
                }
                if(window.selectedTab=='#topDownloadsPerMonthTab')
                {                    
                    window.selectedMonth = $('#filterTopDownloadsMonth').val();
                    loadTopdownloadspermonth();
                }
            });

            $('#clearChartPanel').on('click',function(){
                 $("#payoutCategoryLineChart").empty();
            });

});