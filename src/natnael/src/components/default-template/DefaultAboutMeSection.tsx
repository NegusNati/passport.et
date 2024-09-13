import { Link } from "react-router-dom";

import { IconButton, Typography } from "components/core";
import { TELEGRAM_URL, EMAIL_ADDRESS } from "constants/links";
import TelegramIcon from "icons/TelegramIcon";
import MailIcon from "icons/MailIcon";
import DocsIcon from "icons/Docs";
import { Section } from "shared/Section";

const resumeURL =
  "https://docs.google.com/document/d/1i3NHES51xMxPq2_gydKxreV7U4dPjqDnRlquXT2S6m8/pub";

export const DefaultAboutMeSection = () => {
  return (
    <Section id="about-me" headingText="Natnael Birhanu">
      <div className="animate-hidden flex flex-col w-full gap-[30px] max-w-[650px]">
        <Typography tag="p" weight="semibold" className="text-negus text-xl sm:text-2xl  ">
          Software Engineer
        </Typography>
        <Typography tag="p" className="text-color2 text-l sm:text-xl whitespace-pre-line">
          {
            "Hello and welcome to my portfolio! \n Passionate about Software Engineering, I'm a dedicated and enthusiastic individual with a strong foundation in computer science.\n \n I'm always eager to learn new technologies and techniques to enhance my skills and contribute to innovative projects. I have built two enterprise systems to help a compnay with lead managment and conversion. \n \n I am currently working as a Seinor Full Stack Developer at Victor doors (L and H Building Materials PLC) building in house ERP systems, but also building passport.et at my free time, a solution to a a problem I encountered while trying check if my mother's passport was ready.  \n \n Let's connect and explore woking together!"
          }
        </Typography>
        <div className="flex gap-[12px]">
          <Link to={resumeURL} target="_blank" rel="noopener noreferrer" tabIndex={-1}>
            <IconButton title="Look at my Resume" size="large">
              <DocsIcon />
            </IconButton>
          </Link>
          <Link to={TELEGRAM_URL} target="_blank" rel="noopener noreferrer" tabIndex={-1}>
            <IconButton title="Telegram" size="large">
              <TelegramIcon />
            </IconButton>
          </Link>
          <Link to={EMAIL_ADDRESS} target="_blank" rel="noopener noreferrer" tabIndex={-1}>
            <IconButton title="Email" size="large">
              <MailIcon />
            </IconButton>
          </Link>
        </div>
      </div>
      {/* <div>
      <iframe src="https://docs.google.com/document/d/e/2PACX-1vTytitEB_rchrBxX1IOvbF01rMoE9lDZwgqau3gtEG23NrnkbSzI4J_PAsX4-oFwG4wdJmLjxx57oXU/pub?embedded=true"></iframe>        </div> */}
    </Section>
  );
};
