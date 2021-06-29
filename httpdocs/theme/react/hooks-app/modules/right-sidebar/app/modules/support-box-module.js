import { useState } from 'react';
import UserToolTipModule from '../../../common/user-tooltip-module';

function SupportBoxModule(props){

    
    const data = props.data;
    let section = props.data.section;
    if (props.user && props.user.supportData){
        section = {
            section_id:props.user.supportData.section_id,
            name:props.user.supportData.name
        }
    }

    const [ freeAmount, setFreeAmount ] = useState('');
    const [ chosenAmountField, setChosenAmountField ] = useState('1');

    function onChangeFreeAmount(e){
        setFreeAmount(e.target.value)
    }

    function onFucosFreeAmount(e){
        setChosenAmountField('5');
    }

    function onFormSubmit(event){
        event.preventDefault();
        const $radioVal = $('input[name=amount_predefined]:checked', '#support_form_predefined').val();

        if($radioVal == 'free') {
            const $amount = $(".custom_amount").val();

            if($amount && $amount.length > 0 && $amount > 10.01) {
                $("#support_form_predefined").submit();
                return true;
            } else {
                alert('Please enter a free amount > 10.01');
                return false;
            }
        } else {
            $("#support_form_predefined").submit();
            return true;
        }
    }

    let thankYouForSupportingDisplay = (
        <span className="text-small mt4 mb4">
            Become a 1 year supporter for 1$/month
        </span>
    )
    if (props.user && props.user.isSupporterActive === true && props.user.supportData !== null){
        let adminAsterixDisplay;
        if (props.user.isAdmin === true) adminAsterixDisplay = <a href={"/section?id=" + section.section_id}>*</a>
        thankYouForSupportingDisplay = (
            <span className="text-small mt4 mb4"> 
                Thank you very much for supporting <b>{section.name} {adminAsterixDisplay}</b>  with {"$"+props.user.supportData.tier} per month.
                <hr className="m0"/>
            </span>
        )
    }

    let supportersDisplay;
    if (data.supporters.length > 0){
        supportersDisplay = data.supporters.map((sup,index) => {
            if (index < 9){
                return (
                    <SupporterBoxSupportersModule 
                        key={index}
                        index={index}
                        supporter={sup}
                        onChangeUrl={props.onChangeUrl}
                    />
                )
            }
        });
    }

    return (
        <div className="module-container" id="support-box-module">
            <div className="prod-widget-box prod-user right">
                <div className="sidebar-content">
                    <div className="product-maker-sidebar">
                        <h4 className="section-title">Become a Supporter</h4>
                        <div>
                            <form onSubmit={onFormSubmit} action={"/support-predefined"}  method="POST" id="support_form_predefined">
                                <input type="hidden" name="section_id" value={section.section_id}/>
                                <input type="hidden" name="project_id" value={data.project_id}/>
                                <div>
                                    {thankYouForSupportingDisplay}
                                </div>
                                <div className="supportDiv" >
                                    <button id="add_support" type="submit" className="btn pui-btn pling" role="button" aria-pressed="true">Support</button>                     
                                </div>
                            </form>
                        </div>
                        <div className="support-box-supporters-container">
                            <span className="text-small mt4 mb3">
                                {data.project_title} is part of {section.name}, which is supported by the following people:
                            </span>
                            <hr class="m0" style={{margin:"5px !important"}}/>
                            {supportersDisplay}
                        </div>
                    </div>
                </div>
            </div>
        </div>
   )
}

/* 
<span className="pay-list-container" style={{textAlign: "left"}}>  
    <ul style={{listStyle: "none"}}>
        <li><input onChange={() =>setChosenAmountField('1')} type="radio" name="amount_predefined" id="amount-1" value="0.99" checked={chosenAmountField === '1' ? true : false}/> $0.99</li>
        <li><input onChange={() =>setChosenAmountField('2')} type="radio" name="amount_predefined" id="amount-2" value="2" checked={chosenAmountField === '2' ? true : false} /> $2</li>
        <li><input onChange={() =>setChosenAmountField('3')} type="radio" name="amount_predefined" id="amount-3" value="5"  checked={chosenAmountField === '3' ? true : false}/> $5</li>
        <li><input onChange={() =>setChosenAmountField('4')} type="radio" name="amount_predefined" id="amount-4" value="10"  checked={chosenAmountField === '4' ? true : false}/> $10</li>
        <li><input onChange={() =>setChosenAmountField('5')} type="radio" name="amount_predefined" id="amount-5" value="free"  checked={chosenAmountField === '5' ? true : false}/>
            <span className="amount_predefined"> 
                $ <input onFocus={onFucosFreeAmount} onChange={(e) => onChangeFreeAmount(e)} type="text" step="any" inputMode="numeric" placeholder="Enter a free Amount" name="support_amount" value={freeAmount}/>
            </span>
        </li>
    </ul>
</span>
*/

function SupporterBoxSupportersModule(props){

    const sup = props.supporter;

    function onSupporterClick(e){
        e.preventDefault();
        props.onChangeUrl("/u/"+sup.username,sup.username)
    }

    return (
        <UserToolTipModule 
            toolTipId={"supporter-tool-tip-" + props.index + "-" + sup.member_id}
            toolTipClassName={"supporter-box-list-item-popover-container"}
            username={sup.username}
            memberId={sup.member_id}
            userNameClassName=""
            showUserName={false}
            imgUrl={sup.profile_image_url}
            imgSize={32}
            onUserNameClick={e => onSupporterClick(e)}
            place={"left"}
            layout={"new"}
        />
    )
}

export default SupportBoxModule;