!(function (d3) {

  $("#pie").empty();
  // pie payout

  let yyyymm = $( "#selectmonth option:selected" ).text();

  d3.json("/backend/index/getpayout?yyyymm="+yyyymm, function(error, data) {        
    if (error) throw error;
    data = data.results;
    
    // format the data
    data.forEach(function(d) {
        d.label = d.member_id+'['+d.amount+']';
        d.value = +d.amount;
        d.member = +d.member_id;
    });

    var pie = new d3pie("pie", {
        size: {
          canvasWidth: 590,
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
                //var url='https://opendesktop.org/member/'+a.data.member;
                //window.open(url,'_blank');
                d3.select('#detailContainer').html('');
                let member_id = a.data.member;
                let paypal_mail = a.data.paypal_mail;
                
                d3.json("/backend/index/getpayoutmember?member="+a.data.member, function(error, data) {                  
                            console.log(data);
                            da = data.results;
                            var dh = '<a target="_blank" href="https://opendesktop.org/member/'+member_id+'">'+member_id+'</a>'+paypal_mail;
                            dh= dh+'<table>';       
                            dh=dh+'<tr><td>yyyymm</td><td>Amount</td></tr>';                                                   
                             da.forEach(function(d, i) {
                                  dh=dh+'<tr><td>'+d.yearmonth+'</td><td style="text-align:right">'+d.amount+'</td></tr>';                      
                              });
                             dh=dh+'</table>';
                              d3.select('#detailContainer').html(dh);
  
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