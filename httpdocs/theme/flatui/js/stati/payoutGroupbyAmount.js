!(function (d3) {
            
                    
                d3.json("/backend/index/getpayoutgroupbyamount", function(error, data) {                                            
                if (error) throw error;

             
                data = data.results;   
                if(!data){
                     $("#linePayoutGroupbyAmount").text('no data found!');
                    return;
                }
               

                data.shift();
                data.forEach(function (d) {                   
                   d.year = +d.x;
                   d.amount = +d.y;                      
                });     

                var title ='Product';               
                var chartColumns ={
                    [title]: {column: 'amount'}                       
                };
                            
                var chart = makeLineChartPayoutGroupAmount(data, 'year',chartColumns , {xAxis: 'Tier', yAxis: 'Amount people'});

                $('#linePayoutGroupbyAmount').empty();
                chart.bind("#linePayoutGroupbyAmount");
                chart.render();

            });

            d3.json("/backend/index/getpayoutgroupbyamountmember", function(error, data) {                                            
                if (error) throw error;

             
                data = data.results;   
                if(!data){
                     $("#linePayoutGroupbyAmountMember").text('no data found!');
                    return;
                }
               

                data.shift();
                data.forEach(function (d) {                   
                   d.year = +d.x;
                   d.amount = +d.y;                      
                });     

                var title ='Member';               
                var chartColumns ={
                    [title]: {column: 'amount'}                       
                };
                            
                var chart = makeLineChartPayoutGroupAmount(data, 'year',chartColumns , {xAxis: 'Tier', yAxis: 'Amount people'});

                $('#linePayoutGroupbyAmountMember').empty();
                chart.bind("#linePayoutGroupbyAmountMember");
                chart.render();

            });


})(d3);