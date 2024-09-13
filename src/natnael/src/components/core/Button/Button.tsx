import type { ComponentPropsWithRef, MouseEvent, Ref } from "react";
import { forwardRef } from "react";

import { handleCreateRippleEffect } from "utils/rippleUtils";

import type { ButtonTextAlign } from "./types";

interface ButtonProps extends ComponentPropsWithRef<"button"> {
  fullWidth?: boolean;
  textAlign?: ButtonTextAlign;
}

export const Button = forwardRef(
  (
    { children, fullWidth = false, textAlign = "center", ...props }: ButtonProps,
    ref: Ref<HTMLButtonElement>
  ) => {
    const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
      handleCreateRippleEffect(e);

      props.onClick?.(e);
    };

    return (
      <button
        className={`relative flex items-center bg-primary ${
          textAlign === "center" ? "justify-center" : "justify-start"
        } ${
          fullWidth ? "w-fill" : "w-max"
        } h-[36px] px-4 rounded-md font-medium select-none overflow-hidden duration-200 text-m will-change-transform hover:bg-primary/50 focus-visible:bg-primary/40 focus-visible:shadow-[inset_0px_0px_0px_2px_theme('colors.primary')]`}
        {...props}
        onClick={handleClick}
        ref={ref}
      >
        <span className="z-1">{children}</span>
      </button>
    );
  }
);
