CREATE TABLE [dbo].[tMapStampPicaro] (
    [MapStampPicaroID]     INT           IDENTITY (1, 1) NOT NULL,
    [MapStampPicaroUID]    VARCHAR (100) NULL,
    [InspectionRequestUID] VARCHAR (100) NULL,
    [MapStampUID]          VARCHAR (100) NULL,
    [ProjectID]            INT           NULL,
    [CreatedByUserUID]     VARCHAR (100) NULL,
    [ModifiedByUserUID]    VARCHAR (100) NULL,
    [CreatedDateTime]      DATETIME      CONSTRAINT [DF_tMapStampPicaro_CreatedDateTime] DEFAULT (getdate()) NULL,
    [ModifiedDateTime]     DATETIME      NULL,
    [Revision]             INT           CONSTRAINT [DF_tMapStampPicaro_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]           BIT           CONSTRAINT [DF_tMapStampPicaro_ActiveFlag] DEFAULT ((1)) NULL,
    [PicaroEquipmentID]    VARCHAR (50)  CONSTRAINT [DF_tMapStampPicaro_PicaroEquipmentID] DEFAULT ('') NULL,
    [FeetOfMain]           FLOAT (53)    CONSTRAINT [DF_tMapStampPicaro_FeetOfMain] DEFAULT ((0)) NULL,
    [Services]             INT           CONSTRAINT [DF_tMapStampPicaro_Services] DEFAULT ((0)) NULL,
    [WindSpeedStart]       FLOAT (53)    CONSTRAINT [DF_tMapStampPicaro_WindSpeedStart] DEFAULT ((0)) NULL,
    [WindSpeedMid]         FLOAT (53)    CONSTRAINT [DF_tMapStampPicaro_WindSpeedMid] DEFAULT ((0)) NULL,
    [StatusType]           VARCHAR (200) CONSTRAINT [DF_tMapStampPicaro_StatusType] DEFAULT ('Pending') NULL,
    [SurveyorUID]          VARCHAR (100) CONSTRAINT [DF_tMapStampPicaro_SurveyorUID] DEFAULT ('') NULL,
    [SurveyDate]           DATE          CONSTRAINT [DF_tMapStampPicaro_SurveyDate] DEFAULT (getdate()) NULL,
    [Seq]                  INT           NULL,
    [LockedFlag]           BIT           CONSTRAINT [DF_tMapStampPicaro_LockedFlag] DEFAULT ((0)) NULL,
    [Hours]                FLOAT (53)    CONSTRAINT [DF_tMapStampPicaro_Hours] DEFAULT ((0)) NULL
);





