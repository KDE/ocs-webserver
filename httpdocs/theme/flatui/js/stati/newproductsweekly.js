!(function (d3) {

 
  var margin = {top: 20, right: 20, bottom: 80, left: 40},
      width = 1240 - margin.left - margin.right,
      height = 500 - margin.top - margin.bottom;

  // set the ranges
  var x = d3.scaleBand()
            .range([0, width])
            .padding(0.1);
  var y = d3.scaleLinear()
            .range([height, 0]);
            
  // append the svg object to the body of the page
  // append a 'group' element to 'svg'
  // moves the 'group' element to the top left margin
  var svg = d3.select("#newproductsweekly").append("svg")
      .attr("width", width + margin.left + margin.right)
      .attr("height", height + margin.top + margin.bottom)
    .append("g")
      .attr("transform", 
            "translate(" + margin.left + "," + margin.top + ")");

  // get the data
  d3.json("/backend/index/newproductsweekly", function(error, data) {   
   
      if (error) throw error;
        data = data.results;
    // format the data
    data.forEach(function(d) {
      
      d.amount = +d.amount;    
    });

    // Scale the range of the data in the domains
    x.domain(data.map(function(d) { return d.yyyykw; }));
    y.domain([0, d3.max(data, function(d) { return d.amount; })]);

    // append the rectangles for the bar chart
    svg.selectAll(".bar")
        .data(data)
      .enter().append("rect")
        .attr("class", "bar")
        .attr("x", function(d) { return x(d.yyyykw); })
        .attr("width", x.bandwidth())
        .attr("y", function(d) { return y(d.amount); })
        .attr("height", function(d) { return height - y(d.amount); });

    // add the x Axis
    svg.append("g")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x))
.selectAll("text")
    .attr("y", 0)
    .attr("x", 9)
    .attr("dy", ".35em")
    .attr("transform", "rotate(90)")
    .style("text-anchor", "start");


    // add the y Axis
    svg.append("g")
        .call(d3.axisLeft(y));

  });

})(d3);