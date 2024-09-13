import type { ComponentPropsWithoutRef } from "react";
import { createElement } from "react";

type TypographyTag = "span" | "p" | "h1" | "h2" | "h3" | "h4" | "h5" | "h6" | "div" | "a";
type TypographyWeight = "light" | "normal" | "medium" | "semibold" | "bold";

interface TypographyProps extends ComponentPropsWithoutRef<"span"> {
  tag?: TypographyTag;
  weight?: TypographyWeight;
  center?: boolean;
}

export const Typography = ({
  tag = "span",
  weight = "normal",
  center = false,
  className = "",
  ...props
}: TypographyProps) => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars

  return createElement(tag, {
    className: `font-${weight} text-${center ? "center" : "left"} ${className}`,
    ...props
  });
};
