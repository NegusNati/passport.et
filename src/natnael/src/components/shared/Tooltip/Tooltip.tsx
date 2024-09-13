import type { ComponentPropsWithoutRef, ReactElement } from "react";

import Tippy from "@tippyjs/react";

interface TooltipProps extends Omit<ComponentPropsWithoutRef<typeof Tippy>, "theme"> {
  children: ReactElement;
  content: string;
}

export const Tooltip = ({ children, content, ...props }: TooltipProps) => {
  return content ? (
    <Tippy
      className="bg-gray/30 py-[6px] px-[8px] rounded-md text-xs"
      hideOnClick
      content={content}
      arrow={false}
      touch={["hold", 200]}
      placement="bottom"
      delay={[200, 0]}
      offset={[0, 7]}
      {...props}
    >
      {children}
    </Tippy>
  ) : (
    children
  );
};
