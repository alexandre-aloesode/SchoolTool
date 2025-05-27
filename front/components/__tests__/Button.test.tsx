import React from 'react';
import { render, fireEvent } from '@testing-library/react-native';
import { Pressable, Text } from 'react-native';

const MyButton = ({ label, onPress }) => (
  <Pressable onPress={onPress}>
    <Text>{label}</Text>
  </Pressable>
);

describe('MyButton', () => {
  it('renders the button label', () => {
    const { getByText } = render(
      <MyButton label="Clique-moi" onPress={() => {}} />,
    );
    expect(getByText('Clique-moi')).toBeTruthy();
  });

  it('calls onPress when pressed', () => {
    const onPressMock = jest.fn();
    const { getByText } = render(
      <MyButton label="Clique-moi" onPress={onPressMock} />,
    );
    fireEvent.press(getByText('Clique-moi'));
    expect(onPressMock).toHaveBeenCalledTimes(1);
  });
});
