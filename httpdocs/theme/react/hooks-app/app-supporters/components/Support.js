import React, { useState } from 'react';
const Support = (props) => {
  const [typed, setTyped] = useState();
  const tiers = [0.99, 2, 5, 10, 20, 50];
  const limits = [2, 5, 10, 20, 50, 100];

  const onChangeFreeamount = event => {
    setTyped(event.target.value);
  }

  const container = tiers.map((t, index) => {
      let c;
      let tmp = t;
      let left, right;
      if (index == 0) {
        left = 0;
      } else {
        left = limits[index - 1];
      }
      right = limits[index];
      const result = props.supporters.filter(s => (s.section_support_tier >= left && s.section_support_tier < right));
      return (
        <div className="tier-container" key={index}>
          <span>{result.length + ' Supporters'}</span>
          <div className="join">
            <a href={url}>Join ${t} Tier</a>
          </div>
        </div>
      );
    }
  );

  let result = props.supporters.filter(s => s.section_support_tier >= 100);
  let othertiers;
  let o;

    const x = result.map((s, index) => {
      return (
        <li key={index}>
          <a href={props.baseUrlStore + '/u/' + s.username}><img src={s.profile_image_url}></img></a>
        </li>
      )
    }
    );
    o = <ul>{x}</ul>

    let url = props.baseUrlStore + '/support-predefined?section_id=' + props.section.section_id;
    url = url + '&amount_predefined=' + typed;

    othertiers = (
      <div className="tier-container">
        {o &&
          <span>{x.length} Supporters </span>
        }

        <div className="join">
          <div>
            $<input className="free-amount" onChange={onChangeFreeamount}></input><span>100 or more</span>
          </div>
          <div>
            <a href={url} id="free-amount-link" >Join </a>
          </div>
        </div>
      </div>
    )

  return (
    <div className="support-container">
        <div className="tiers">
          <h5>Tiers</h5>
        </div>
        {container}
        {othertiers}
      </div>
  )
}

export default Support

