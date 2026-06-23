import { createContext, useContext, useRef } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, View } from 'react-native';

const KeyboardAwareScrollContext = createContext(null);

export function useKeyboardAwareScroll() {
  return useContext(KeyboardAwareScrollContext);
}

export default function KeyboardAwareScrollView({ children, contentContainerStyle, ...props }) {
  const scrollRef = useRef(null);
  const contentRef = useRef(null);

  function scrollToFocusedInput(inputRef) {
    setTimeout(() => {
      if (!inputRef.current || !contentRef.current || !scrollRef.current) {
        return;
      }

      inputRef.current.measureLayout(
        contentRef.current,
        (_x, y) => {
          scrollRef.current?.scrollTo({ y: Math.max(y - 80, 0), animated: true });
        },
        () => {}
      );
    }, 80);
  }

  return (
    <KeyboardAvoidingView
      style={styles.fill}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <KeyboardAwareScrollContext.Provider value={scrollToFocusedInput}>
        <ScrollView
          ref={scrollRef}
          keyboardShouldPersistTaps="handled"
          keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
          automaticallyAdjustKeyboardInsets={Platform.OS === 'ios'}
          contentInsetAdjustmentBehavior="automatic"
          contentContainerStyle={contentContainerStyle}
          {...props}
        >
          <View ref={contentRef}>
            {children}
          </View>
        </ScrollView>
      </KeyboardAwareScrollContext.Provider>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  fill: {
    flex: 1,
  },
});
