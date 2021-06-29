import './style/loading-spinners.css';

function LoadingSpinner(props){

    let spinnerDisplay = <LoadingSpinnerDots/>
    if (props.type === "dots") spinnerDisplay = <LoadingSpinnerDots/>
    else if (props.type === "ripples") spinnerDisplay = <LoadingSpinnerRipples/>
    else if (props.type === "circle") spinnerDisplay = <LoadingSpinnerCircle/>
    else if (props.type === "classic") spinnerDisplay = <LoadingSpinnerClassic/>

    return (
        <div className="loading-spinner-container">
            <div className="spinner-wrapper">
                {spinnerDisplay}
            </div>
            <div className="spinner-msg">
                {props.msg}
            </div>
        </div>
    )
}

const LoadingSpinnerDots = (props) => {
    return (
        <div className="lds-grid">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    )
}

const LoadingSpinnerRipples = (props) => {
    return (
        <div className="lds-ripple">
            <div></div>
            <div></div>
        </div>
    )
}

const LoadingSpinnerCircle = (props) => {
    return (
        <div className="lds-circle">
            <div></div>
        </div>
    )
}

const LoadingSpinnerClassic = (props) => {
    return (
        <div className="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    )
}

export default LoadingSpinner;