import type { MouseEvent } from "react";

export const handleCreateRippleEffect = (e: MouseEvent<HTMLElement>, color?: string) => {
  const { currentTarget, clientX, clientY } = e;

  if (currentTarget) {
    const { left, top } = currentTarget.getBoundingClientRect();
    const x = clientX - left;
    const y = clientY - top;
    const { clientHeight, clientWidth } = currentTarget;
    const rippleSize = Math.max(clientHeight, clientWidth, 100);

    const circle = document.createElement("span");

    const center = x < 0 || y < 0;

    circle.style.width = `${rippleSize}px`;
    circle.style.height = `${rippleSize}px`;
    circle.style.backgroundColor = color ?? "customColor";
    circle.style.left = center ? "" : `${x - rippleSize / 2}px`;
    circle.style.top = center ? "" : `${y - rippleSize / 2}px`;
    circle.classList.add("ripple");

    setTimeout(() => circle.remove(), 450);

    currentTarget.appendChild(circle);
  }
};
