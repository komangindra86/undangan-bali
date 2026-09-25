// Feed state kept outside the screen so the list and its scroll position survive navigation.
export const feedSession = {
  hasMore: true,
  items: [],
  page: 1,
  photoIndexes: {},
  scrollOffset: 0,
  version: 0,
};

// Called by the detail screen so counts shown in the feed match what the user just did.
export function patchFeedItem(id, patch) {
  let changed = false;
  feedSession.items = feedSession.items.map((item) => {
    if (item.id !== id) return item;
    changed = true;
    return { ...item, ...patch };
  });
  if (changed) feedSession.version += 1;
}

// Called after the viewer blocks a Moment owner so the card disappears without a full refresh.
export function removeFeedItem(id) {
  const nextItems = feedSession.items.filter((item) => item.id !== id);
  if (nextItems.length === feedSession.items.length) return;
  feedSession.items = nextItems;
  feedSession.version += 1;
}
