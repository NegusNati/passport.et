import type { ReactNode } from "react";
import { useState } from "react";

import { Button, CustomLink, Typography } from "components/core";
import ExternalLinkIcon from "icons/ExternalLinkIcon";

interface DefaultContactItemProps {
  icon: ReactNode;
  title: string;
  text?: string;
  href?: string;
  buttonText?: string;
  onClick?: () => void;
}

export const DefaultContactItem = ({
  icon,
  title,
  text,
  href,
  buttonText,
  onClick
}: DefaultContactItemProps) => {
  const [isClicked, setIsClicked] = useState(false);
  return (
    <div className="animate-hidden flex gap-[30px] sm:gap-[40px]">
      <div className="flex [&>svg]:h-[40px] [&>svg]:w-[40px] [&>svg]:text-color2/60 hover:text-negus  transition duration-100 ease-in-out hover:animate-ping">
        {icon}
      </div>
      <div className="flex flex-col gap-[20px]">
        <Typography weight="medium" className="text-xl sm:text-2xl">
          {title}
        </Typography>
        {text && <Typography className="text-color2 text-m sm:text-l">{text}</Typography>}
        {onClick && (
          <Button
            onClick={() => {
              onClick();
              setIsClicked(!isClicked);
            }}
          >
            {" "}
            {isClicked ? `🎉 Copied 🎉` : buttonText}{" "}
          </Button>
        )}
        {href && (
          <CustomLink
            href={href}
            icon={<ExternalLinkIcon />}
            target="_blank"
            rel="noopener noreferrer"
          >
            Connect
          </CustomLink>
        )}
      </div>
    </div>
  );
};
