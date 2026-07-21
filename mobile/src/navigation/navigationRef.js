import { createNavigationContainerRef } from '@react-navigation/native';

export const navigationRef = createNavigationContainerRef();

export function navigateFromPush(data, attempts = 0) {
  if (!navigationRef.isReady()) {
    if (attempts < 12) {
      setTimeout(() => navigateFromPush(data, attempts + 1), 250);
    }
    return;
  }

  const invitationId = Number(data?.invitation_id);
  if (data?.screen === 'InvitationRequests' && invitationId > 0) {
    navigationRef.navigate('InvitationRequests', { invitation: { id: invitationId } });
    return;
  }

  navigationRef.navigate('Notifications');
}
