import type { ComponentPropsWithRef, MouseEvent, ReactElement, Ref } from "react";
import { cloneElement } from "react";
import { forwardRef } from "react";

import { Tooltip } from "shared/Tooltip";
import { handleCreateRippleEffect } from "utils/rippleUtils";

type IconButtonSize = "medium" | "large";

interface IconButtonProps extends ComponentPropsWithRef<"button"> {
  children: ReactElement;
  title: string;
  fullWidth?: boolean;
  size?: IconButtonSize;
}

export const IconButton = forwardRef(
  (
    { children, title = "", size = "medium", ...props }: IconButtonProps,
    ref: Ref<HTMLButtonElement>
  ) => {
    const isLarge = size === "large";

    const handleClick = (e: MouseEvent<HTMLButtonElement>) => {
      handleCreateRippleEffect(e, "#4615b2");

      props.onClick?.(e);
    };

    return (
      <Tooltip content={title}>
        <button
          className={`relative flex justify-center align-center ${
            isLarge ? "p-[8px]" : "p-[7px]"
          } text-color1 rounded-full overflow-hidden duration-200 hover:bg-primary/50 focus-visible:bg-primary/40 focus-visible:shadow-[0px_0px_0px_2px_theme('colors.primary')]`}
          type="button"
          aria-label={title}
          {...props}
          onClick={handleClick}
          ref={ref}
        >
          {cloneElement(children, {
            className: `self-center ${isLarge ? "w-[32px] h-[32px]" : "w-[22px] h-[22px]"}`
          })}
        </button>
      </Tooltip>
    );
  }
);
