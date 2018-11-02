!(function (d3) {

  $("#pie").empty();
  $('#detailContainer').empty();
  // pie payout  
  
  var yyyymm = $( "#selectmonth option:selected" ).text();

  d3.json("/backend/index/getpayout?yyyymm="+yyyymm, function(error, data) {        
    if (error) throw error;
    data = data.results;
    if(!data){
        $("#pie").text("no data found ! "+yyyymm); 
        return;
    }
    
    // format the data
    data.forEach(function(d) {
        //d.label = d.member_id+'['+d.amount+']';
        d.label = d.username+'['+d.amount+']';
        d.value = +d.amount;
        d.member = +d.member_id;
        d.username = d.username;      
    });

    var pie = new d3pie("pie", {
        size: {
          canvasWidth: 650,
          pieOuterRadius: "90%"           
        },
        header: {
            title: {
              text: "Member payout_"+yyyymm,
              fontSize: 24,
              font: "open sans"
              }
          },
        data: {
          sortOrder: "value-desc",
          smallSegmentGrouping: {
            enabled: true,
            value: 1,
            valueType: "percentage",
            label: "<2%"
          },
          content: data
        },
      callbacks: {
          onClickSegment: function(a) {
            if(!a.data.isGrouped)
            {
                
                d3.select('#detailContainer').html('');
                var member_id = a.data.member;
                var paypal_mail = a.data.paypal_mail;
                var username = a.data.username;

                var parseTime = d3.timeParse("%Y%m");
                d3.json("/backend/index/getpayoutmember?member="+a.data.member, function(error, data) {               if (error) throw error;
                            // console.log(data);
                            // da = data.results;
                            // var dh = '<div style="display:block;float:left"><a target="_blank" href="https://opendesktop.org/member/'+member_id+'">'+username + '['+member_id+'] '+'</a>'+paypal_mail+'</div>';
                            // // dh= dh+'<table>';       
                            // // dh=dh+'<tr><td>yyyymm</td><td>Amount</td></tr>';                                                   
                            // //  da.forEach(function(d, i) {
                            // //       dh=dh+'<tr><td>'+d.yearmonth+'</td><td style="text-align:right">'+d.amount+'</td></tr>';                      
                            // //   });
                            // //  dh=dh+'</table>';
                          //d3.select('#detailContainer').html(dh);

                      $('#detailContainer').append('<div class="chart-wrapper" id="detailContainerLinechart"></div>');
                            data = data.results;
                           data.forEach(function (d) {                   
                             d.year = parseTime(d.yearmonth);
                             d.amount = +d.amount;                               
                          });                             
                            var columnName = username+'['+member_id+']';
                              var chartColumns ={
                                  [columnName]: {column: 'amount'}                       
                              };
                            var chart = makeLineChart(data, 'year',chartColumns , {xAxis: 'Month', yAxis: 'Amount'});                          
                            chart.bind("#detailContainerLinechart");
                            chart.render();

                         
  
                  });
            }else{
                console.log(a);
                d3.select('#detailContainer').html('');

                var dh='<table>';
                 var da = a.data.groupedData;                   
                 dh=dh+'<tr><td>#'+da.length+'</td><td>'+a.data.value+'</td></tr>';
                 da.forEach(function(d, i) {
                      dh=dh+'<tr><td><a target="_blank" href="https://opendesktop.org/member/'+d.member+'">'+d.member+'</a></td><td>'+d.value+'</td></tr>';                      
                  });
                 dh=dh+'</table>';
                d3.select('#detailContainer').html(dh);
            }              
          }
          /*,
          onMouseoverSegment: function(info) {
                    console.log("mouseover:", info);
                    d3.select('#detailContainer').html('');
                    if(!info.data.isGrouped){
                          d3.json("/backend/index/getpayoutmember?member="+info.data.member, function(error, data) { 
                                console.log(data);

                          });
                    }
            }
            */
        }
      });

  

  });



})(d3);