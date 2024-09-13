import type { ComponentPropsWithRef, MouseEvent, Ref } from "react";
import { forwardRef } from "react";

import { handleCreateRippleEffect } from "utils/rippleUtils";

import type { ButtonTextAlign } from "./types";

interface ButtonScrollToProps extends ComponentPropsWithRef<"button"> {
  elementId: string;
  textAlign?: ButtonTextAlign;
}

export const ButtonScrollTo = forwardRef(
  (
    { children, elementId, textAlign = "center", ...props }: ButtonScrollToProps,
    ref: Ref<HTMLButtonElement>
  ) => {
    const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
      const element = document.getElementById(elementId);

      if (element) {
        element.scrollIntoView({ behavior: "smooth" });
      }

      handleCreateRippleEffect(e, "#4615b2");
      props.onClick?.(e);
    };

    return (
      <button
        {...props}
        className={`relative flex items-center w-max ${
          textAlign === "center" ? "justify-center" : "justify-start"
        } h-[36px] px-4 rounded-md
        font-medium select-none overflow-hidden duration-200 text-m will-change-transform hover:bg-primary/50 focus-visible:bg-primary/40 focus-visible:shadow-[inset_0px_0px_0px_2px_theme('colors.primary')]`}
        onClick={handleClick}
        ref={ref}
      >
        <span className="z-1">{children}</span>
      </button>
    );
  }
);
