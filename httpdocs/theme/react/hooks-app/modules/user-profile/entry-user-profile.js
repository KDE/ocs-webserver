import ReactDOM from 'react-dom';
import UserProfile from './user-profile';
import UserProfileContextProvider from './context/context-provider';

function UserProfileWrapper(){
    return (
        <UserProfileContextProvider>
            <UserProfile/>
        </UserProfileContextProvider>
    )
}

ReactDOM.render(<UserProfileWrapper />, document.getElementById('user-profile-container'));
