import UserProfileHeader from './partials/user-profile-header';
import UserProfileTabs from './partials/user-profile-tabs';

import './style/user-profile.css';

function UserProfile(props){
    return (
        <div className="user-profile-body">
            <React.Fragment>
                <UserProfileHeader />
                <hr className="divider"/>
                <UserProfileTabs
                    onChangeUrl={props.onChangeUrl}
                />
            </React.Fragment>
        </div>
    )
}

export default UserProfile;