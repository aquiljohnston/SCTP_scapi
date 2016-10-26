CREATE TABLE [dbo].[tgWindSpeed] (
    [WindSpeedID]          INT                IDENTITY (1, 1) NOT NULL,
    [WindSpeedUID]         VARCHAR (100)      NULL,
    [InspectionRequestUID] VARCHAR (100)      NULL,
    [ProjectID]            INT                NULL,
    [SourceID]             VARCHAR (50)       NULL,
    [CreatedUserUID]       VARCHAR (100)      NULL,
    [ModifiedUserUID]      VARCHAR (100)      NULL,
    [srcDTLT]              DATETIME           NULL,
    [srvDTLT]              DATETIME           CONSTRAINT [DF_tgWindSpeed_srvDTLT] DEFAULT (getdate()) NULL,
    [srvDTLTOffset]        DATETIMEOFFSET (7) CONSTRAINT [DF_tgWindSpeed_srvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [Comments]             VARCHAR (500)      NULL,
    [Revision]             INT                CONSTRAINT [DF_tgWindSpeed_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]           BIT                CONSTRAINT [DF_tgWindSpeed_ActiveFlag] DEFAULT ((1)) NULL,
    [WindSpeed]            FLOAT (53)         NULL,
    [Latitude]             FLOAT (53)         NULL,
    [Longitude]            FLOAT (53)         NULL,
    [EntryTime]            TIME (7)           NULL,
    [MapPlat]              VARCHAR (20)       NULL,
    [MapGridUID]           VARCHAR (100)      NULL,
    [AlertFlag]            BIT                NULL,
    [SurveyType]           VARCHAR (200)      NULL
);

